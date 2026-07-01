<?php

// Register hooks for scanning
add_action( 'wpmc_scan_once', 'wpmc_scan_once_acf', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_acf', 10, 2 );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_acf', 10, 1 );

// Constants
define( 'WPMC_ACF_MAX_RECURSION', 100 ); // Hard limit to prevent infinite loops

/**
 * Initial scan for ACF - run once per overall scan
 * Handles options pages, taxonomies, and collects ACF block types
 */
function wpmc_scan_once_acf() {
    // Collect all ACF blocks for later reference
    $wpmc_acf_blocks = array();
    
    // Scan ACF options page
    wpmc_scan_postmeta_acf( 'options' );
    
    // Scan taxonomy terms with ACF fields
    wpmc_scan_once_taxonomies_acf();

    // Handle ACF Blocks registration
    if ( function_exists( 'acf_get_block_types' ) ) {
        $blocks = acf_get_block_types();
        foreach ( $blocks as $block ) {
            $wpmc_acf_blocks[] = $block['name'];
        }
    }

    // Store blocks for reference during post scanning
    set_transient( 'wpmc_acf_blocks', $wpmc_acf_blocks, MONTH_IN_SECONDS );
}

/**
 * Scans taxonomy terms for ACF fields
 */
function wpmc_scan_once_taxonomies_acf() {
    global $wpdb;
    global $wpmc;
    
    // Find all terms that have termmeta (potential ACF data)
    $terms = $wpdb->get_results( "SELECT x.term_id, x.taxonomy 
        FROM {$wpdb->term_taxonomy} x, {$wpdb->termmeta} y 
        WHERE x.term_id = y.term_id 
        GROUP BY x.term_id, x.taxonomy"
    );

    // Check if $terms is empty or WP_Error
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        $wpmc->log( "Error retrieving terms for ACF scan: " . ( is_wp_error( $terms ) ? $terms->get_error_message() : 'No terms found' ) );
        return; // No terms found or error occurred
    }
    
    foreach ( $terms as $term ) {
        $termStr = $term->taxonomy . '_' . $term->term_id;
        $fields = get_field_objects( $termStr );
        
        if ( !empty( $fields ) && is_array( $fields ) ) {
            foreach ( $fields as $field ) {
                wpmc_scan_postmeta_acf_field( $field, $termStr, 16 );
            }
        }
    }
}

/**
 * Process post content for ACF blocks
 * 
 * @param string $html Post content HTML
 * @param int $id Post ID
 */
function wpmc_scan_html_acf( $html, $id ) {
    global $wpmc;
    $images_ids = array();
    $images_urls = array();

    $post = get_post( $id );
    if ( !$post || !has_blocks( $post ) ) {
        return;
    }
    
    $blocks = parse_blocks( $post->post_content );
    if ( empty( $blocks ) || !is_array( $blocks ) ) {
        return;
    }
    
    // Get registered ACF blocks
    $wpmc_acf_blocks = get_transient( 'wpmc_acf_blocks' );
    if ( !is_array( $wpmc_acf_blocks ) ) {
        $wpmc_acf_blocks = array();
    }
    
    // Process all blocks
    foreach ( $blocks as $block ) {
        // Handle direct ACF blocks (registered)
        if ( isset( $block['blockName'] ) && in_array( $block['blockName'], $wpmc_acf_blocks ) ) {
            if ( isset( $block['attrs']['data'] ) ) {
                wpmc_scan_acf_block( $block['attrs']['data'], $images_ids, $images_urls );
            }
        } else {
            // Fallback: if transient is empty or block name starts with 'acf/',
            // or the block contains attrs.data - treat as ACF-like and scan recursively
            $is_acf_like = false;
            if ( empty( $wpmc_acf_blocks ) && isset( $block['blockName'] ) && strpos( $block['blockName'], 'acf/' ) === 0 ) {
                $is_acf_like = true;
            }
            if ( isset( $block['attrs']['data'] ) ) {
                $is_acf_like = true;
            }
            if ( $is_acf_like ) {
                wpmc_scan_acf_block( $block['attrs']['data'] ?? array(), $images_ids, $images_urls );
            }
        }
        
        // Handle reusable blocks that contain ACF blocks
        if ( isset( $block['blockName'] ) && $block['blockName'] === 'core/block' && isset( $block['attrs']['ref'] ) ) {
            $reusable_post = get_post( $block['attrs']['ref'] );
            if ( $reusable_post ) {
                $block_content = parse_blocks( $reusable_post->post_content );
                if ( is_array( $block_content ) ) {
                    foreach ( $block_content as $b ) {
                        if ( isset( $b['attrs']['data'] ) ) {
                            wpmc_scan_acf_block( $b['attrs']['data'], $images_ids, $images_urls );
                        }
                    }
                }
            }
        }
    }

    // Deduplicate
    $images_ids = array_unique( array_filter( $images_ids ) );
    $images_urls = array_unique( array_filter( $images_urls ) );

    $images_urls = apply_filters( 'wpmc_acf_urls', $images_urls, $images_ids, $id );
    $images_ids  = apply_filters( 'wpmc_acf_ids', $images_ids, $images_urls, $id );

    // Add found references to the cleaner
    if ( !empty( $images_ids ) ) {
        $wpmc->add_reference_id( $images_ids, 'ACF BLOCK (ID)', $id );
    }
    if ( !empty( $images_urls ) ) {
        $wpmc->add_reference_url( $images_urls, 'ACF BLOCK (URL)', $id );
    }
}

/**
 * Analyze an ACF Block to find media references (recursive)
 * 
 * @param mixed $data Block data from the block parser (can be array, string, or numeric)
 * @param array &$images_ids Array to collect image IDs
 * @param array &$images_urls Array to collect image URLs
 */
function wpmc_scan_acf_block( $data, &$images_ids, &$images_urls ) {
    global $wpmc;
    
    if ( empty( $data ) ) {
        return;
    }
    
    // If it's a simple number - it might be an attachment ID
    if ( is_numeric( $data ) ) {
        $images_ids[] = intval( $data );
        return;
    }
    
    // If it's a string - check whether it contains a URL
    if ( is_string( $data ) ) {
        // Simple heuristic match for URL or img src
        if ( strpos( $data, 'http' ) !== false ) {
            $images_urls[] = $wpmc->clean_url( $data );
        }
        return;
    }
    
    // If it's an array - iterate through it
    if ( is_array( $data ) ) {
        foreach ( $data as $k => $v ) {
            // If we have a pair _field => 'field_xxx' and next to it field => numeric, process it
            if ( is_string( $k ) && strlen( $k ) > 0 && $k[0] === '_' ) {
                $realKey = ltrim( $k, '_' );
                if ( isset( $data[ $realKey ] ) && is_numeric( $data[ $realKey ] ) ) {
                    $images_ids[] = intval( $data[ $realKey ] );
                    continue;
                }
            }
            // Recursively enter nested structures
            wpmc_scan_acf_block( $v, $images_ids, $images_urls );
        }
    }
}

/**
 * Entry point for scanning post meta with ACF
 * 
 * @param int|string $id Post ID or 'options' for options page
 */
function wpmc_scan_postmeta_acf( $id ) {
    $fields = get_field_objects( $id );
    if ( is_array( $fields ) ) {
        foreach ( $fields as $field ) {
            wpmc_scan_postmeta_acf_field( $field, $id, 16 );
        }
    }
}

/**
 * Process complex nested field structures like groups, repeaters, etc.
 * 
 * @param array $block Field or block data to process
 * @param int|string $id Post ID or identifier
 * @param array &$postmeta_images_acf_ids Array to collect image IDs
 * @param array &$postmeta_images_acf_urls Array to collect image URLs
 * @param int $recursion_limit Limit to prevent infinite recursion
 */
function wpmc_scan_postmeta_acf_field_block( $block, $id, &$postmeta_images_acf_ids,
    &$postmeta_images_acf_urls, $recursion_limit = -1 ) { 
    // Track recursion depth
    static $global_recursion_count = 0;
    $global_recursion_count++;
    
    // Prevent infinite loops
    if ( $global_recursion_count > WPMC_ACF_MAX_RECURSION ) {
        error_log( 'Media Cleaner: ACF recursion limit reached (' . WPMC_ACF_MAX_RECURSION . ') - stopping scan to prevent infinite loop' );
        $global_recursion_count--;
        return;
    }
    
    // Check recursion limit (per branch)
    if ( $recursion_limit === 0 ) {
        $global_recursion_count--;
        return;
    }
    
    // Process field or recurse into nested fields
    if ( is_array( $block ) && isset( $block['type'] ) ) {
        // This is a field definition, process it
        wpmc_scan_postmeta_acf_field( $block, $id, $recursion_limit - 1 );
    }
    else if ( is_array( $block ) ) {
        // This is a container of fields, process each one
        foreach ( $block as $value ) {
            wpmc_scan_postmeta_acf_field_block( $value, $id, $postmeta_images_acf_ids,
                $postmeta_images_acf_urls, $recursion_limit - 1 );
        }
    }
    
    $global_recursion_count--;
}

/**
 * Scans a single ACF field object for media references
 * 
 * @param array $field ACF field object
 * @param int|string $id Post ID or identifier
 * @param int $recursion_limit Limit to prevent infinite recursion
 */
function wpmc_scan_postmeta_acf_field( $field, $id, $recursion_limit = -1 ) {
    // Track recursion depth
    static $global_recursion_count = 0;
    $global_recursion_count++;
    
    // Prevent infinite loops
    if ( $global_recursion_count > WPMC_ACF_MAX_RECURSION ) {
        error_log( 'Media Cleaner: ACF recursion limit reached (' . WPMC_ACF_MAX_RECURSION . ') - stopping scan to prevent infinite loop' );
        $global_recursion_count--;
        return;
    }
    
    // Skip invalid fields
    if ( !isset( $field['type'] ) ) {
        $global_recursion_count--;
        return;
    }
    
    global $wpmc;
    
    // Define array of field types that could contain nested fields
    $recursive_field_types = array(
        'repeater',
        'flexible_content',
        'group',
        'clone',
    );
    
    // Arrays to collect media references
    $postmeta_images_acf_ids = array();
    $postmeta_images_acf_urls = array();
    
    // Get the return format if available
    $format = "";
    if ( isset( $field['return_format'] ) ) {
        $format = $field['return_format'];
    } else if ( isset( $field['save_format'] ) ) {
        $format = $field['save_format'];
    }
    
    // Check if this is a recursive field type
    $is_recursive = in_array( $field['type'], $recursive_field_types );
    
    // For flexible content fields, we need to process each layout
    if ( $field['type'] === 'flexible_content' && !empty( $field['value'] ) ) {
        // Process each layout in the flexible content field
        foreach ( $field['value'] as $layout ) {
            if ( !is_array( $layout ) ) {
                continue;
            }
            
            // Process the layout as a group of subfields
            foreach ( $layout as $key => $value ) {
                // Skip ACF internal fields
                if ( $key === 'acf_fc_layout' ) {
                    continue;
                }
                
                // Try to get subfield object
                $subfield = get_field_object( $key, $id );
                $subfield_type = isset( $subfield['type'] ) ? $subfield['type'] : '';

                if ( is_array( $subfield ) && !empty( $subfield_type ) && !in_array( $subfield_type, $recursive_field_types ) ) {
                    // Process this subfield
                    wpmc_scan_postmeta_acf_field( $subfield, $id, $recursion_limit - 1 );
                } 
                
                if ( is_array( $value ) ) {
                    // Handle values directly when field object isn't available
                    wpmc_extract_media_from_value( $value, $postmeta_images_acf_ids, $postmeta_images_acf_urls );
                }
            }
        }
    }
    // For repeater fields with existing rows
    else if ( $is_recursive && have_rows( $field['name'], $id ) ) {
        if ( $recursion_limit == 0 ) {
            $global_recursion_count--;
            return; // Too much recursion
        }
        
        // Process each row in the repeater
        do {
            $row = the_row( true );
            foreach ( $row as $col => $value ) {
                $subfield = get_sub_field_object( $col, true, true );
                if ( !is_array( $subfield ) ) {
                    continue;
                }
                wpmc_scan_postmeta_acf_field( $subfield, $id, $recursion_limit - 1 );
            }
        } while ( have_rows( $field['name'], $id ) );
        
        $global_recursion_count--;
        return;
    }
    // For groups and other nested structures
    else if ( $is_recursive && is_array( $field['value'] ) ) {
        wpmc_scan_postmeta_acf_field_block( $field['value'], $id, $postmeta_images_acf_ids,
            $postmeta_images_acf_urls, $recursion_limit - 1 );
        
        $global_recursion_count--;
        return;
    }
    
    // Skip fields that don't contain media
    if ( in_array( $field['type'], [ 'color_picker' ] ) ) {
        $global_recursion_count--;
        return;
    }
    
    // Process different field types
    switch ( $field['type'] ) {
        // Image field
        case 'image':
            wpmc_process_image_field( $field, $format, $postmeta_images_acf_ids, $postmeta_images_acf_urls );
            break;
            
        // Gallery field
        case 'gallery':
            wpmc_process_gallery_field( $field, $format, $postmeta_images_acf_ids, $postmeta_images_acf_urls );
            break;
            
        // Photo gallery (3rd party extension)
        case 'photo_gallery':
            wpmc_process_photo_gallery_field( $field, $postmeta_images_acf_ids, $postmeta_images_acf_urls );
            break;
            
        // WYSIWYG editor content
        case 'wysiwyg':
            wpmc_process_wysiwyg_field( $field, $postmeta_images_acf_urls );
            break;
            
        // File field (could be any file type)
        case 'file':
            wpmc_process_file_field( $field, $postmeta_images_acf_ids, $postmeta_images_acf_urls );
            break;
            
        // Aspect ratio crop (3rd party extension)
        case 'image_aspect_ratio_crop':
            wpmc_process_aspect_ratio_crop_field( $field, $postmeta_images_acf_ids );
            break;
            
        // Clone field
        case 'clone':
            wpmc_process_clone_field( $field, $postmeta_images_acf_ids );
            break;
            
        // Repeater with direct access to values
        case 'repeater':
            wpmc_process_repeater_field( $field, $postmeta_images_acf_ids );
            break;
            
        // URL field (could contain video URL)
        case 'url':
            wpmc_process_url_field( $field, $postmeta_images_acf_urls );
            break;

        case 'file':
            wpmc_process_file_field( $field, $postmeta_images_acf_ids, $postmeta_images_acf_urls );
            break;
        

		// Add more field types here as needed
		default:
			error_log( 'Media Cleaner: Unhandled ACF field type: ' . $field['type'] );
			break;
    }
    
    // Register found references
    if ( !empty( $postmeta_images_acf_ids ) ) {
        $wpmc->add_reference_id( $postmeta_images_acf_ids, 'ACF (ID)', $id );
    }
    if ( !empty( $postmeta_images_acf_urls ) ) {
        $wpmc->add_reference_url( $postmeta_images_acf_urls, 'ACF (URL)', $id );
    }
    
    $global_recursion_count--;
}

/**
 * Extract media IDs and URLs from a value array
 * 
 * @param array $value Value array that might contain media references
 * @param array &$ids Array to collect IDs
 * @param array &$urls Array to collect URLs
 */
function wpmc_extract_media_from_value( $value, &$ids, &$urls ) {
    global $wpmc;
    
    if ( !is_array( $value ) ) {
        return;
    }
    
    // Look for common patterns in arrays that might contain media
    if ( isset( $value['id'] ) && is_numeric( $value['id'] ) ) {
        $ids[] = $value['id'];
    }
    
    if ( isset( $value['url'] ) && !empty( $value['url'] ) ) {
        $urls[] = $wpmc->clean_url( $value['url'] );
    }
    
    // Check for URLs that might be in various other keys
    $url_keys = ['src', 'full_url', 'thumbnail_url', 'medium_url', 'large_url', 'full_image_url'];
    foreach ( $url_keys as $key ) {
        if ( isset( $value[$key] ) && !empty( $value[$key] ) ) {
            $urls[] = $wpmc->clean_url( $value[$key] );
        }
    }
    
    // Recursively check nested arrays
    foreach ( $value as $item ) {
        if ( is_array( $item ) ) {
            wpmc_extract_media_from_value( $item, $ids, $urls );
        }
    }
}

/**
 * Process an ACF image field
 */
function wpmc_process_image_field( $field, $format, &$ids, &$urls ) {
    global $wpmc;
    
    if ( empty( $field['value'] ) ) {
        return;
    }
    
    // Format: array or object
    if ( $format == 'array' || $format == 'object' ) {
        if ( is_object( $field['value'] ) && get_class( $field['value'] ) == 'Timber\Image' ) {
            // Timber image object
            if ( !empty( $field['value']->id ) ) {
                $ids[] = $field['value']->id;
            }
            if ( !empty( $field['value']->src() ) ) {
                $urls[] = $wpmc->clean_url( $field['value']->src() );
            }
        } elseif ( is_array( $field['value'] ) ) {
            // Standard array format
            if ( !empty( $field['value']['id'] ) ) {
                $ids[] = $field['value']['id'];
            }
            if ( !empty( $field['value']['url'] ) ) {
                $urls[] = $wpmc->clean_url( $field['value']['url'] );
            }
        }
    } 
    // Format: id
    elseif ( $format == 'id' ) {
        $ids[] = $field['value'];
    } 
    // Format: url
    elseif ( $format == 'url' ) {
        $urls[] = $wpmc->clean_url( $field['value'] );
    }
    // From Block (has status)
    elseif ( isset( $field['status'] ) && $field['status'] == 'inherit' ) {
        $ids[] = $field['id'];
        $urls[] = $wpmc->clean_url( $field['url'] );
    }
}

/**
 * Process an ACF gallery field
 */
function wpmc_process_gallery_field( $field, $format, &$ids, &$urls ) {
    global $wpmc;
    
    if ( empty( $field['value'] ) || !is_array( $field['value'] ) ) {
        return;
    }
    
    foreach ( $field['value'] as $media ) {
        // Handle different return formats
        if ( $format === 'url' && is_string( $media ) ) {
            $urls[] = $wpmc->clean_url( $media );
        }
        elseif ( $format === 'id' && is_numeric( $media ) ) {
            $ids[] = $media;
        }
        elseif ( $format === 'array' && is_array( $media ) && isset( $media['id'] ) ) {
            $ids[] = $media['id'];
            
            // Also get URL if available
            if ( isset( $media['url'] ) ) {
                $urls[] = $wpmc->clean_url( $media['url'] );
            }
        }
    }
}

/**
 * Process an ACF photo gallery field (3rd party extension)
 */
function wpmc_process_photo_gallery_field( $field, &$ids, &$urls ) {
    global $wpmc;
    
    if ( empty( $field['value'] ) || !is_array( $field['value'] ) ) {
        return;
    }
    
    foreach ( $field['value'] as $media ) {
        if ( isset( $media['id'] ) ) {
            $ids[] = $media['id'];
        }
        
        // Get all available image sizes
        $url_keys = ['full_image_url', 'thumbnail_image_url', 'medium_image_url', 'large_image_url'];
        foreach ( $url_keys as $key ) {
            if ( isset( $media[$key] ) ) {
                $urls[] = $wpmc->clean_url( $media[$key] );
            }
        }
    }
}

/**
 * Process an ACF WYSIWYG field
 */
function wpmc_process_wysiwyg_field( $field, &$urls ) {
    global $wpmc;
    
    if ( empty( $field['value'] ) ) {
        return;
    }
    
    // Extract URLs from HTML content
    $extracted_urls = $wpmc->get_urls_from_html( $field['value'] );
    foreach ( $extracted_urls as $url ) {
        $urls[] = $url;
    }
}

/**
 * Process an ACF file field
 */
function wpmc_process_file_field( $field, &$ids, &$urls ) {
    global $wpmc;
    
    if ( empty( $field['value'] ) ) {
        return;
    }
    
    $value = $field['value'];
    
    // Handle array format (with url key)
    if ( is_array( $value ) && isset( $value['url'] ) ) {
        $value = $value['url'];
    }
    
    // Store by ID or URL based on return format
    if ( isset( $field['return_format'] ) && $field['return_format'] == 'id' ) {
        $ids[] = $value;

        // We need to find the URL for the ID In case it's a filesystem scan
        $attachment = get_post( $value );
        if ( $attachment && isset( $attachment->guid ) ) {
            $urls[] = $wpmc->clean_url( $attachment->guid );
        }
    } else {
        $urls[] = $wpmc->clean_url( $value );
    }
}

/**
 * Process an ACF aspect ratio crop field (3rd party extension)
 */
function wpmc_process_aspect_ratio_crop_field( $field, &$ids ) {
    if ( empty( $field['value'] ) || !is_array( $field['value'] ) ) {
        return;
    }
    
    // Store both the cropped image and original image
    if ( isset( $field['value']['id'] ) ) {
        $ids[] = $field['value']['id']; // Latest crop
    }
    
    if ( isset( $field['value']['original_image']['id'] ) ) {
        $ids[] = $field['value']['original_image']['id']; // Original image
    }
}

/**
 * Process an ACF clone field
 */
function wpmc_process_clone_field( $field, &$ids ) {
    if ( !is_array( $field['value'] ) ) {
        return;
    }
    
    // Handle media in clone fields
    if ( isset( $field['value']['media_type'] ) && $field['value']['media_type'] === 'image'
         && isset( $field['value']['media_image']['id'] ) ) {
        $ids[] = $field['value']['media_image']['id'];
    }
}

/**
 * Process an ACF repeater field with direct access to values
 */
function wpmc_process_repeater_field( $field, &$ids ) {
    if ( !is_array( $field['value'] ) ) {
        return;
    }
    
    // Look for image IDs directly in repeater rows
    foreach ( $field['value'] as $value ) {
        if ( isset( $value['image'] ) && is_numeric( $value['image'] ) ) {
            $ids[] = $value['image'];
        }
    }
}

/**
 * Process an ACF URL field (could be video URL)
 */
function wpmc_process_url_field( $field, &$urls ) {
    global $wpmc;
    
    if ( empty( $field['value'] ) || is_array( $field['value'] ) ) {
        return;
    }
    
    // Add URL to the list
    $urls[] = $wpmc->clean_url( $field['value'] );
}
