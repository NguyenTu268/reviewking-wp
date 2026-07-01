<?php
// Adding action hooks for Media Cleaner plugin
add_action('wpmc_scan_once', 'wpmc_scan_once_houzez', 10, 0);
add_action('wpmc_scan_post', 'wpmc_scan_html_houzez', 10, 2);
add_action('wpmc_scan_postmeta', 'wpmc_scan_postmeta_houzez', 10, 2);

/**
 * Runs once at the beginning of the scan.
 * Can be used to check images usage in general settings, in a theme, like a favicon, etc.
 */
function wpmc_scan_once_houzez()
{
    global $wpmc;

    $postmeta_images_ids  = array( );
    $postmeta_images_urls = array( );

    $houzez_options            = get_option( 'houzez_options' );
    $houzez_options_transients = get_option( 'houzez_options-transients' );

    $attributes = [ 'custom_logo', 'url', 'thumbnail' ];

    $wpmc->get_from_meta( $houzez_options, $attributes, $postmeta_images_ids, $postmeta_images_urls );
    $wpmc->get_from_meta( $houzez_options_transients, $attributes, $postmeta_images_ids, $postmeta_images_urls) ;

    // Add image references to the Media Cleaner
    $wpmc->add_reference_id(  $postmeta_images_ids,  'Houzez Settings (ID)'  );
    $wpmc->add_reference_url( $postmeta_images_urls, 'Houzez Settings (URL)' );
}

/**
 * Runs for each postmeta of any post type.
 * Scans and collects image IDs and URLs from post meta.
 *
 * @param int $id The post ID.
 */
function wpmc_scan_postmeta_houzez( $id )
{
    global $wpmc;
    $postmeta_images_ids = array( );

    $data = get_post_meta( $id, 'fave_property_images', false );
    if  ( empty( $data ) ) { return; }

    foreach ($data as $value) {
        if ( is_numeric( $value ) ) {
            $postmeta_images_ids[] = $value;
        } elseif ( is_array( $value ) ) {
            foreach ( $value as $v ) {
                if ( is_numeric( $v ) ) {
                    $postmeta_images_ids[] = $v;
                }
            }
        }
    }

    foreach ($postmeta_images_ids as $image_id) {
        $urls = $wpmc->get_thumbnails_urls_from_srcset( intval( $image_id ) );
        $wpmc->add_reference_url($urls, 'Houzez (URL) {SAFE}', $id);
    }

    // Add image references to the Media Cleaner
    $wpmc->add_reference_id($postmeta_images_ids, 'Houzez (ID)', $id);

}

/**
 * Runs for each post of any post type.
 * Scans and collects image URLs from the post content (HTML).
 *
 * @param string $html The post content HTML.
 * @param int $id The post ID.
 */
function wpmc_scan_html_houzez($html, $id)
{

}