<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_elementor', 10, 1 );

function wpmc_scan_postmeta_elementor( $id ) {
	global $wpmc;
	$ids = array();
	$urls = array();

  	$data = get_post_meta( $id, '_elementor_data' );
	if ( isset( $data[0] ) ) {

		if ( is_array( $data[0] ) ) {
			error_log( "Media Cleaner: Elementor data is an array (not supported yet), Post ID: $id" );
		}
		else {
			$decoded = json_decode( $data[0] );
			$wpmc->get_from_meta( $decoded, array( 'id', 'url', 'background_image' ), $ids, $urls );
		}
	}

	$settings = get_post_meta( $id, '_elementor_page_settings', true );
	if ( !empty( $settings ) && is_array( $settings ) ) {
		$wpmc->get_from_meta( $settings, array( 'id', 'url', 'background_image' ), $ids, $urls );
	}


	$wpmc->add_reference_id( $ids, 'ELEMENTOR (ID)', $id );
	$wpmc->add_reference_url( $urls, 'ELEMENTOR (URL)', $id );
}

?>