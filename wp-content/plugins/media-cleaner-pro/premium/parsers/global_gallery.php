<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_global_gallery', 10, 1 );

function wpmc_scan_postmeta_global_gallery( $id ) {
  $type = get_post_type( $id );
  if ( $type !== 'gg_galleries' ) {
    return;
  }

  // Get the gallery data based on the ID (Global Gallery plugin)
  $data = get_post_meta( $id, 'gg_gallery', true );
  if ( !is_array( $data ) && !empty( $data ) ) {
    $string = base64_decode( $data );
    if ( function_exists( 'gzcompress' ) && function_exists( 'gzuncompress' ) && !empty( $string ) ) {
      $string = gzuncompress( $string );
    }
    $data = (array)unserialize( $string );
  }

  if ( !is_array( $data ) || ( count( $data ) == 1 && !$data[0] ) ) {
    $data = false;
  }

  global $wpmc;
  $postmeta_images_ids = array();
	$postmeta_images_urls = array();
	if ( !empty( $data ) ) {
		$wpmc->get_from_meta( $data, array( 'img_src' ), $postmeta_images_ids, $postmeta_images_urls );
  }
  $wpmc->add_reference_id( $postmeta_images_ids, 'Global Gallery', $id );
	$wpmc->add_reference_url( $postmeta_images_urls, 'Global Gallery', $id );
}

?>