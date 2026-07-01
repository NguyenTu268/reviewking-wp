<?php
// Adding action hooks for Media Cleaner plugin
add_action('wpmc_scan_once', 'wpmc_scan_once_wp_download_manager', 10, 0);
add_action('wpmc_scan_post', 'wpmc_scan_html_wp_download_manager', 10, 2);
add_action('wpmc_scan_postmeta', 'wpmc_scan_postmeta_wp_download_manager', 10, 2);


function wpmc_scan_once_wp_download_manager()
{

}


function wpmc_scan_postmeta_wp_download_manager( $id )
{
    global $wpmc;

    $urls = array( );
    $ids  = array( );

    $files = get_post_meta( $id, '__wpdm_files', true );
    if ( is_array( $files ) && ! empty( $files ) ) {
        foreach ( $files as $file ) {
            $url = $wpmc->clean_url( $file );
            $urls[] = $url;
        }
    }

    $previews = get_post_meta( $id, '__wpdm_additional_previews', true );
    if ( is_array( $previews ) && ! empty( $previews ) ) {
        foreach ( $previews as $preview ) {
            $id = intval ( $preview );
            if ( $id > 0 ) {
                $ids[] = $id;
            }
        }
    }


    $wpmc->add_reference_url( $urls, 'WP Download Manager', $id );
    $wpmc->add_reference_id( $ids, 'WP Download Manager', $id );
    
}


function wpmc_scan_html_wp_download_manager($html, $id)
{

}