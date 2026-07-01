<?php

add_action('wpmc_scan_once', 'wpmc_scan_once_foo_gallery', 10, 0);
add_action('wpmc_scan_post', 'wpmc_scan_html_foo_gallery', 10, 2);

function wpmc_scan_once_foo_gallery()
{
    global $wpdb, $wpmc;
    // Get from database all the foogallery attachments post meta
    $postmeta_images_ids = array();
    $postmeta_images_urls = array();

    $meta = $wpdb->get_results(
        "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'foogallery_attachments'"
    );

    foreach ( $meta as $item ) {
        $decoded = maybe_unserialize( $item->meta_value );
        if ( is_array( $decoded ) ) {
            $wpmc->array_to_ids_or_urls( $decoded, $postmeta_images_ids, $postmeta_images_urls );
        }
    }

    $postmeta_images_ids = array_unique( $postmeta_images_ids );

    // Foo Gallery uses the media full size URL by default, so let's add them to the reference, they might not be used in the content so we use the {SAFE} tag
    $postmeta_images_urls = array_map( function( $id ) use ( $wpmc ) {
        $url = wp_get_attachment_url( $id );
        $url = $wpmc->clean_url( $url );
        return $url;
    }, $postmeta_images_ids );

    $wpmc->add_reference_id( $postmeta_images_ids, 'Foo Gallery (ID)' );
    $wpmc->add_reference_url( $postmeta_images_urls, 'Foo Gallery (URL) {SAFE}' );
}


function wpmc_scan_html_foo_gallery( $html, $id )
{
    global $wpmc;

    if( !$wpmc->get_shortcode_analysis() ) {
        $wpmc->log( '↪️ Skipping Foo Gallery parser on Post ID ' . $id . ' because shortcode analysis is disabled.' ); 
        return;
    }
    
    // Get the FooGallery blocks
    $block_matches = array();
    $prefix = 'fooplugins/foogallery';
    $blocks = parse_blocks( $html );
    foreach ( $blocks as $block ) {

        if( empty( $block ) ) continue;
        if( !array_key_exists( 'blockName', $block ) ) continue;

        if ( strpos( $block['blockName'], $prefix ) === false ) {
            continue;
        }

        $block_id = isset( $block['attrs']['id'] ) ? $block['attrs']['id'] : '';
        //Create a shortcode [foogallery id="{block_id}"] and add it to the matches array
        $shortcode = '[foogallery id="' . esc_attr( $block_id ) . '"]';
        $block_matches[] = $shortcode;
    }


    $matches = array();
    $pattern = get_shortcode_regex( ['foogallery', 'foogallery-album'] );
    preg_match_all( '/'. $pattern .'/s', $html, $matches );

    // Merge block matches and shortcode matches
    $matches[0] = array_merge( $matches[0], $block_matches );


    //* Maybe do that in the scan once? This will only work if the shortcode is present in the post content ( no builder )
    $converted_from_album_shortcodes = array();
    foreach( $matches[0] as $shortcode ) { 
        // Convert Album to gallery shortcodes, [foogallery-album id="25"] we get the ID ( 25 ) and then we get the post_meta of this ID with key foogallery_album_galleries, this returns an array of gallery IDs, then we create a shortcode [foogallery id="GALLERY_ID"] for each gallery ID
        if ( strpos( $shortcode, 'foogallery-album' ) !== false ) {
            preg_match( '/id=["\'](.*?)["\']/', $shortcode, $id_match );
            if ( isset( $id_match[1] ) ) {
                $album_id = intval( $id_match[1] );
                $gallery_ids = get_post_meta( $album_id, 'foogallery_album_galleries', true );
                if ( is_array( $gallery_ids ) ) {
                    foreach ( $gallery_ids as $gallery_id ) {
                        $shortcode = '[foogallery id="' . esc_attr( $gallery_id ) . '"]';
                        $converted_from_album_shortcodes[] = $shortcode;
                    }
                }
            }
        }
    }

    // Merge block matches and shortcode matches
    $matches[0] = array_merge( $matches[0], $converted_from_album_shortcodes );

    $already_processed = array();
    foreach( $matches[0] as $shortcode ) {

        // Set the attribute "paging_type" to "" in the shortcode to avoid pagination, to see all medias in the output
        if ( strpos( $shortcode, 'paging_type' ) === false ) {
            if ( strpos( $shortcode, ']') !== false ) {
                $shortcode = str_replace( ']', ' paging_type=""]', $shortcode );
            }
        } else {
            $shortcode = preg_replace( '/paging_type=["\'].*?["\']/', 'paging_type=""', $shortcode );
        }

        if ( in_array( $shortcode, $already_processed ) ) {
            continue;
        }

        $inner_html = do_shortcode( $shortcode );
        $already_processed[] = $shortcode;

        $urls = array();

        if( strpos( $inner_html, 'script type="text/javascript"' ) !== false ) {
            // Some layouts of Foo Gallery output a script to dynamically render the gallery, so the parse HTML won't contain the URLs
            $found = $wpmc->get_urls_from_string( $inner_html );
            $urls = array_merge( $urls, $found );
            
            //continue;
        }

        $found = get_urls_from_inner_html_foo_gallery( $inner_html );
        $urls = array_merge( $urls, $found );

        foreach ( $urls as $url ) {
            if( strpos( $url, 'cache/' ) === 0 ) {
                // FooGallery caches media like: cache/2025/12/filename/4058337598.avif
                // So we need to remove the "cache/" part and the hash but keep the extension
                $no_cache_ref = str_replace( 'cache/', '', $url );
                $no_cache_ref = preg_replace( '/(\/[0-9]+\.('. $wpmc->types .'))$/i', '.$2', $no_cache_ref );

                $wpmc->add_reference_url( $no_cache_ref, 'Foo Gallery (URL) {CACHE}', $id );
            }

            $wpmc->add_reference_url( $url, 'Foo Gallery (URL)', $id );
        }

        
    }

    // Get the Foo Gallery album shortcodes
}


function get_urls_from_inner_html_foo_gallery( $html ) {
    if( empty( $html ) ) return array();

    global $wpmc;

    $urls = array();
    $dom = new DOMDocument();
    @$dom->loadHTML( $html );

    $xpath = new DOMXPath($dom);
    $attributes = ['data-src-fg', 'src', 'href'];

    foreach ($attributes as $attr) {
        $nodes = $xpath->query('//@' . $attr);
        foreach ($nodes as $node) {
            
            $url = $node->nodeValue;
            $url = $wpmc->clean_url( $url );

            if ( !empty( $url ) ) {
                array_push( $urls, $url );
            }
        }
    }

    return $urls;
}

?>