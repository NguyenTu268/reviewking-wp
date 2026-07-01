<?php

add_action( 'wpmc_scan_post', 'wpmc_scan_html_visualcomposer', 10, 2 );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_visualcomposer', 10, 1 );

function wpmc_scan_html_visualcomposer( $html, $id ) {

	global $wpmc;
	$posts_images_vc = array();
	$galleries_images_vc = array();



	// Support for Salient Theme

	if ( defined( 'NECTAR_THEME_NAME' ) ) {
		$ids = [];
		$urls = [];

		$nodes = $wpmc->nested_shortcodes_to_array( $html );
		$wpmc->array_to_ids_or_urls( $nodes, $ids, $urls, true, [ "image_url" ] );

		// Add the IDs and URLs to the references
		$wpmc->add_reference_id( $ids, 'SALIENT ELEMENT (ID)', $id );
		//For each ID, we should set the thumbnail URLs to SAFE
		foreach ($ids as $image_id) {
			$urls = $wpmc->get_thumbnails_urls_from_srcset( intval( $image_id ) );
			$wpmc->add_reference_url($urls, 'SALIENT ELEMENT (URL) {SAFE}', $id);
		}

		$extra = get_post_meta( $id, '_nectar_portfolio_extra_content', true );
		$urls = $wpmc->get_urls_from_html( $extra );
		if ( !empty( $urls ) ) {
			$wpmc->add_reference_url( $urls, 'SALIENT META (URLS)', $id );
		}

		$header_bg = get_post_meta( $id, '_nectar_header_bg', true );
		if ( !empty( $header_bg ) ) {
			$header_bg = $wpmc->clean_url( $header_bg );
			$wpmc->add_reference_url( $header_bg, 'SALIENT META (URLS)', $id );
		}
	}

	// Background image by ID
	preg_match_all( "/id\^([0-9]+)\|/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) && count( $res[1] ) > 0 ) {
		//error_log( print_r( $res, 1 ) );
		foreach ( $res[1] as $id ) {
			array_push( $posts_images_vc, $id );
		}
	}
	$wpmc->add_reference_id( $posts_images_vc, 'WP Bakery (Background)', $id );


	// Single Image
	preg_match_all( "/(image(_id)?|media)=\"([0-9]+)\"/", $html, $res );
	if ( !empty( $res ) && isset( $res[3] ) && count( $res[3] ) > 0 ) {
		foreach ( $res[3] as $id ) {
			array_push( $posts_images_vc, $id );
		}
	}
	$wpmc->add_reference_id( $posts_images_vc, 'PAGE BUILDER (ID)', $id );

	// Gallery
	preg_match_all( "/(images|medias)=\"([0-9,]+)/", $html, $res );
	if ( !empty( $res ) && isset( $res[2] ) ) {
		foreach ( $res[2] as $r ) {
			$ids = explode( ',', $r );
			$galleries_images_vc = array_merge( $galleries_images_vc, $ids );
		}
	}
	$wpmc->add_reference_id( $galleries_images_vc, 'WP Bakery (Gallery)', $id );
}

function wpmc_scan_postmeta_visualcomposer( $id ) {
	global $wpmc;
	$postmeta_images_ids = array();
	$postmeta_images_urls = array();

	$wpb_shortcodes_custom_css = get_post_meta( $id, '_wpb_shortcodes_custom_css' );
	if ( is_array( $wpb_shortcodes_custom_css ) ) {
		foreach ( $wpb_shortcodes_custom_css as $d ) {
			$newurls = $wpmc->get_urls_from_html( $d );
			$postmeta_images_urls = array_merge( $postmeta_images_urls, $newurls );
		}
	}

	$vc_post_settings = get_post_meta( $id, '_vc_post_settings' );
	if ( is_array( $vc_post_settings ) ) {
		$wpmc->get_from_meta( $vc_post_settings, array( 'include' ), $postmeta_images_ids, $postmeta_images_urls );
		//error_log( print_r( $vc_post_settings, 1 ) );
	}
	
	$wpmc->add_reference_id( $postmeta_images_ids, 'WP Bakery (META)', $id );
	$wpmc->add_reference_url( $postmeta_images_urls, 'WP Bakery (META)', $id );
}

?>