<?php
// Adding action hooks for Media Cleaner plugin
add_action('wpmc_scan_once', 'wpmc_scan_once_w3_total_cache', 10, 0);


function wpmc_scan_once_w3_total_cache()
{
    global $wpmc;

    $args = [
        'post_type' => 'attachment',
        'post_status' => 'any',
        'meta_query' => [
            [
                'key' => 'w3tc_imageservice_file',
                'value' => 'webp',
                'compare' => 'LIKE'
            ]
        ]
    ];

    $attachments = get_posts( $args );
    if ( empty( $attachments ) ) {
        return;
    }

    $postmeta_images_ids = array();
    $postmeta_images_urls = array();

    foreach ( $attachments as $attachment ) {
        $postmeta_images_ids[] = $attachment->ID;

        $url = wp_get_attachment_url( $attachment->ID );
        $url = $wpmc->clean_url( $url );

        $path = '';
        $last_slash_pos = strrpos( $url, '/' );
        if ( $last_slash_pos !== false ) {
            $path = substr( $url, 0, $last_slash_pos + 1 );
        }
        
        $postmeta_images_urls[] = $url;

        $postmeta_images_sizes = array();
        $postmeta_images_sizes_ids = array();
        $meta = get_post_meta( $attachment->ID, '_wp_attachment_metadata', true );
        $decoded = maybe_unserialize( $meta );
        if ( is_array( $decoded ) ) {
            $wpmc->array_to_ids_or_urls( $decoded, $postmeta_images_sizes_ids, $postmeta_images_sizes, true );
        }

        foreach( $postmeta_images_sizes as $filename ) {
            if( strpos( $filename, $path ) !== false ) {
                continue;
            }

            $postmeta_images_urls[] = $path . $filename;
        }
    }

    $postmeta_images_ids = array_unique( $postmeta_images_ids );
    $postmeta_images_urls = array_unique( $postmeta_images_urls );

    $wpmc->add_reference_id( $postmeta_images_ids, 'W3 Total Cache (ID)' );
    $wpmc->add_reference_url( $postmeta_images_urls, 'W3 Total Cache (URL) {SAFE}' );
    
}
