<?php

add_action('wpmc_scan_postmeta', 'wpmc_scan_postmeta_toolset', 10, 2);



function wpmc_scan_postmeta_toolset($id)
{
    global $wpmc;
    $postmeta_images_urls = array();

    global $wpdb;
    $postmeta_table = $wpdb->prefix . 'postmeta';
    $res = $wpdb->get_results( $wpdb->prepare(
        "SELECT meta_value FROM $postmeta_table WHERE post_id = %d AND meta_key LIKE %s",
        $id,
        'wpcf-%'
    ) );
    
    foreach ( $res as $row ) {
        $urls = $wpmc->get_urls_from_string( $row->meta_value );
        $postmeta_images_urls = array_merge( $postmeta_images_urls, $urls );
    }

    // Add image references to the Media Cleaner
    $wpmc->add_reference_url($postmeta_images_urls, 'Toolset Types (URL)', $id);
}