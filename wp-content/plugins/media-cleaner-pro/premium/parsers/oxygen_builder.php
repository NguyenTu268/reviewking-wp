<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_oxygen', 10, 0 );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_oxygen', 10, 1 );

function wpmc_scan_once_oxygen() {
  global $wpmc;
  
  $ct_components = get_option( '_ct_components_classes', '' );
  if ( empty( $ct_components ) ) $ct_components = get_option( 'ct_components_data', '' );

  if ( !empty( $ct_components ) ) {
    $postmeta_images_ids = array();
	  $postmeta_images_urls = array();
    $wpmc->get_from_meta( $ct_components, array( 'background-image' ), $postmeta_images_ids, $postmeta_images_urls );
    $wpmc->add_reference_id( $postmeta_images_ids, 'CT Oxygen (ID)' );
	  $wpmc->add_reference_url( $postmeta_images_urls, 'CT Oxygen (URL)' );
  }
}	

function wpmc_scan_postmeta_oxygen( $id ) {
  global $wpmc;

  // * New Oxygen (Oxygen Beta)
  $oxygen_data = get_post_meta( $id, '_oxygen_data', true );
  $oxygen_data = json_decode( $oxygen_data, true );
  if ( !empty( $oxygen_data ) ) {
    $urls = $wpmc->get_urls_from_string( $oxygen_data['tree_json_string'] );
    $wpmc->add_reference_url( $urls, 'New Oxygen (URL)', $id );
  }
  

  //* Oxygen Builder
  $oxygen_builder_data = get_post_meta( $id, '_ct_builder_json', true );
  if( !empty( $oxygen_builder_data ) ) {
    $urls = $wpmc->get_urls_from_string( $oxygen_builder_data );
    $wpmc->add_reference_url( $urls, 'Oxygen Builder (URL)', $id );
  }


  //* Older Oxygen versions
  $ct_data = get_post_meta( $id, '_ct_builder_shortcodes', true );
  if ( empty( $ct_data ) ) $ct_data = get_post_meta( $id, 'ct_builder_shortcodes', true );
  if ( empty( $ct_data ) ) return;

  // Detect the background images
  $urls = $wpmc->get_urls_from_string( $ct_data );
  $wpmc->add_reference_url( $urls, 'CT Oxygen (URL)', $id );

  // Detect the content of the shortcodes
  if( !$wpmc->get_shortcode_analysis() ) return;
  
  if ( shortcode_exists( $ct_data ) ) {
    $html = do_shortcode( $ct_data );
    $postmeta_images_urls = $wpmc->get_urls_from_html( $html );
    $wpmc->add_reference_url( $postmeta_images_urls, 'CT Oxygen (URL)', $id );
  }
}

?>