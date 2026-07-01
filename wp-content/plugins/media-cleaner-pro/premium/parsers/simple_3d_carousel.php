<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_simple_3d_carousel', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_simple_3d_carousel', 10, 2 );

function wpmc_scan_once_simple_3d_carousel() {
  global $wpmc;

	$data = get_option( 'fwds3dcar_data' );
	if ( !empty( $data ) ) {
		$postmeta_images_ids = array();
	  $postmeta_images_urls = array();
    $wpmc->get_from_meta( $data, array( 'path' ), $postmeta_images_ids, $postmeta_images_urls );
		$wpmc->add_reference_id( $postmeta_images_ids, 'CAROUSEL (ID)' );
	  $wpmc->add_reference_url( $postmeta_images_urls, 'CAROUSEL (URL)' );
	}
}	

?>