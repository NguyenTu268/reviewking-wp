<?php
// Adding action hooks for Media Cleaner plugin
add_action('wpmc_scan_once', 'wpmc_scan_once_layerslider', 10, 0);


/**
 * Runs once at the beginning of the scan.
 * Can be used to check images usage in general settings, in a theme, like a favicon, etc.
 */
function wpmc_scan_once_layerslider()
{
    global $wpmc;
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'layerslider';
    $results = $wpdb->get_results("SELECT data FROM $table_name", ARRAY_A);

    
    foreach ( $results as $row ) {
        $json_data = json_decode( $row['data'], true );
        $slider_name = "Slider: " . ( isset( $json_data['properties']['title'] ) ? $json_data['properties']['title'] : 'UNKNOWN' );

        $urls = $wpmc->get_urls_from_string( $row['data'] );
        $urls = array_unique( $urls );
        $wpmc->add_reference_url( $urls, 'LayerSlider', $slider_name );

        foreach ( $urls as $url ) {
            $srcset_urls = $wpmc->get_thumbnails_urls_from_srcset( $url );
            $srcset_urls = array_unique( $srcset_urls );

            $wpmc->add_reference_url( $srcset_urls, 'LayerSlider {SAFE}', $slider_name );
        }

    }    
}

