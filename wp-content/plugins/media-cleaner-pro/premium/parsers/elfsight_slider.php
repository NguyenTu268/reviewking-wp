<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_elfsight_slider', 10, 0 );

function wpmc_scan_once_elfsight_slider() {
  global $wpmc;
  global $wpdb;

  $table = $wpdb->get_blog_prefix() . 'elfsight_slider_widgets';
  $options = $wpdb->get_col( "SELECT options FROM $table" );
  foreach ( $options as $option ) {
    $data = json_decode( $option );
    $postmeta_images_ids = array();
    $postmeta_images_urls = array();
    $wpmc->get_from_meta( $data, array( 'media' ), $postmeta_images_ids, $postmeta_images_urls );
    $wpmc->add_reference_id( $postmeta_images_ids, 'SLIDER (ID)' );
	  $wpmc->add_reference_url( $postmeta_images_urls, 'SLIDER (URL)' );
  }
}

?>