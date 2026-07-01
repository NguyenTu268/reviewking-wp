<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_nimble_builder', 10, 1 );

function wpmc_scan_postmeta_nimble_builder( $id ) {
	$type = get_post_type( $id );
  if ( $type !== 'nimble_post_type' ) {
    return;
  }
	global $wpmc;

	$postmeta_images_ids = array();
	$postmeta_images_urls = array();
	$content = unserialize( get_post_field( 'post_content', $id ) );
	if ( !empty( $content ) ) {
		$wpmc->get_from_meta( $content, array( 'img' ), $postmeta_images_ids, $postmeta_images_urls );
	}
	$wpmc->add_reference_id( $postmeta_images_ids, 'PAGE BUILDER META (ID)', $id );
	$wpmc->add_reference_url( $postmeta_images_urls, 'PAGE BUILDER META (URL)', $id );
}

?>