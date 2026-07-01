<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_breakdance_builder', 10, 2 );

/**
 * Scans postmeta for images and URLs used by the Breakdance builder.
 * 
 * @param int $id The ID of the post being scanned.
 */
function wpmc_scan_postmeta_breakdance_builder( $id ) {
  global $wpmc;
  $postmeta_images_ids = array();
  $postmeta_images_urls = array();
  $data = get_post_meta( $id, 'breakdance_data', true );

  // Check if $data is not false or empty before decoding
  if ( !empty( $data ) ) {
    $data = json_decode( $data, true );
    // Check if json_decode was successful and 'tree_json_string' exists
    if ( is_array( $data ) && isset( $data['tree_json_string'] ) ) {
      $tree_json_string = $data['tree_json_string'];
      $tree_json = json_decode( $tree_json_string, true );
      // Ensure $tree_json is an array before proceeding
      if ( is_array( $tree_json ) ) {
        $attributes = array( 'id', 'url', 'thumbnail' );
        $wpmc->get_from_meta( $tree_json, $attributes, $postmeta_images_ids, $postmeta_images_urls );
        $wpmc->add_reference_id( $postmeta_images_ids, 'BREAKDANCE BUILDER (ID)', $id );
        $wpmc->add_reference_url( $postmeta_images_urls, 'BREAKDANCE BUILDER (URL)', $id );
      }
    }
  }
}

?>