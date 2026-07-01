<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_web_stories', 10, 1 );

function wpmc_scan_postmeta_web_stories( $id ) {
  global $wpmc;
  $type = get_post_type( $id );
  if ( $type !== 'web-story' ) {
    return;
  }
  $content = get_post_field( 'post_content_filtered', $id, 'raw' );
  $json = !empty( $content ) ? json_decode( $content ) : null;
  if ( !empty( $json ) ) {
    $postmeta_images_ids = array();
	  $postmeta_images_urls = array();
    $wpmc->get_from_meta( $json, array( 'id', 'file' ), $postmeta_images_ids, $postmeta_images_urls );
    $wpmc->add_reference_id( $postmeta_images_ids, 'WEB STORIES (ID)', $id );
	  $wpmc->add_reference_url( $postmeta_images_urls, 'WEB STORIES (URL)', $id );
  }
}

?>