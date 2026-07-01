<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_fusionbuilder', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_fusionbuilder', 10, 2 );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_fusionbuilder', 10, 2 );

function wpmc_scan_once_fusionbuilder_get_option( $option ) {
	$res = get_option( $option );
	return is_array( $res ) ? $res : array();
}

function wpmc_scan_once_fusionbuilder() {
	global $wpmc;
	$options = array();

	$default_languages = [ '', 'en', 'all' ];
	$languages = $wpmc->get_languages();
	$languages = array_merge( $languages, $default_languages );

	$options[] = wpmc_scan_once_fusionbuilder_get_option( 'fusion_options' );
	foreach ( $languages as $language ) {
		$options[] = wpmc_scan_once_fusionbuilder_get_option( 'fusion_options_' . $language );
	}
	
	foreach ( $options as $option ) {
		$postmeta_images_ids = array();
		$postmeta_images_urls = array();
		//error_log( print_r( $option, 1 ) );
		$wpmc->get_from_meta( $option, array( 'url', 'thumbnail' ), $postmeta_images_ids, $postmeta_images_urls );
		$wpmc->add_reference_id( $postmeta_images_ids, 'AVADA PORTFOLIO (ID)' );
		$wpmc->add_reference_url( $postmeta_images_urls, 'AVADA PORTFOLIO (URL)' );
		//error_log( print_r( $postmeta_images_urls ) );
	}
}

function wpmc_scan_postmeta_fusionbuilder( $id ) {
  global $wpmc;
  $postmeta_images_ids = array();
	$postmeta_images_urls = array();
	$data = get_post_meta( $id, '_fusion' );

	// FusionBuilder is doing this horrible thing, not using an array to store the IDs used in the portfolio
	// but named attributes. It's limited to 30 (!?) so let's just look into all this.
	$attributes = array();
	for ( $c = 0; $c < 30; $c++ ) {
		array_push( $attributes, 'kd_featured-image-' . ($c + 1) . '_avada_portfolio_id' );
	}
  $wpmc->get_from_meta( $data, $attributes, $postmeta_images_ids, $postmeta_images_urls );
  $wpmc->add_reference_id( $postmeta_images_ids, 'AVADA PORTFOLIO (ID)', $id );
	$wpmc->add_reference_url( $postmeta_images_urls, 'AVADA PORTFOLIO (URL)', $id );
}


function wpmc_scan_fusion_builder_matches_urls($html, $keyword, array &$targetArray, $wpmc) {
	$pattern = "/" . $keyword . "=\"((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(" . $wpmc->types . "))\"/";
    preg_match_all($pattern, $html, $res);
    if (!empty($res) && isset($res[1]) && count($res[1]) > 0) {
        foreach ($res[1] as $url) {
            $targetArray[] = $wpmc->clean_url($url);
        }
    }
}

function wpmc_scan_fusion_builder_matches_ids($html, $keyword, array &$targetArray) {
	$pattern = "/" . $keyword . "=\"([0-9,]+)/";
	preg_match_all($pattern, $html, $res);
	if (!empty($res) && isset($res[1])) {
		foreach ($res[1] as $r) {
			$ids = explode(',', $r);
			$targetArray = array_merge($targetArray, $ids);
		}
	}
}

function wpmc_scan_html_fusionbuilder( $html, $id ) {
	global $wpmc;
	$posts_images_urls = array();
	$galleries_images = array();

	// Images between brackets
	preg_match_all( "/\]((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(" . $wpmc->types . "))\[\//", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) && count( $res[1] ) > 0 ) {
		foreach ( $res[1] as $url ) {
			array_push( $posts_images_urls, $wpmc->clean_url( $url ) );
		}
	}

	// Background Image
	wpmc_scan_fusion_builder_matches_urls($html, 'background_image', $posts_images_urls, $wpmc);
	wpmc_scan_fusion_builder_matches_ids($html, 'background_image_id', $galleries_images);

	// Image
	wpmc_scan_fusion_builder_matches_urls($html, 'image', $posts_images_urls, $wpmc);
	wpmc_scan_fusion_builder_matches_ids($html, 'image_ids', $galleries_images);
	wpmc_scan_fusion_builder_matches_ids($html, 'image_id', $galleries_images);

	
	// background_image_front
	wpmc_scan_fusion_builder_matches_urls($html, 'background_image_front', $posts_images_urls, $wpmc);
	wpmc_scan_fusion_builder_matches_ids($html, 'background_image_id_front', $galleries_images);

	// background_image_back
	wpmc_scan_fusion_builder_matches_urls($html, 'background_image_back', $posts_images_urls, $wpmc);
	wpmc_scan_fusion_builder_matches_ids($html, 'background_image_id_back', $galleries_images);

	// background_image_medium
	wpmc_scan_fusion_builder_matches_urls($html, 'background_image_medium', $posts_images_urls, $wpmc);
	wpmc_scan_fusion_builder_matches_ids($html, 'background_image_id_medium', $galleries_images);

	// background_image_small
	wpmc_scan_fusion_builder_matches_urls($html, 'background_image_small', $posts_images_urls, $wpmc);
	wpmc_scan_fusion_builder_matches_ids($html, 'background_image_id_small', $galleries_images);

	// video_preview_image
	wpmc_scan_fusion_builder_matches_urls($html, 'video_preview_image', $posts_images_urls, $wpmc);

	//background_slider_images
	wpmc_scan_fusion_builder_matches_urls($html, 'background_slider_images', $posts_images_urls, $wpmc);
	
	

	$wpmc->add_reference_url( $posts_images_urls, 'AVADA BUILDER (URL)', $id );
	$wpmc->add_reference_id( $galleries_images, 'AVADA BUILDER (ID)', $id );
}

?>