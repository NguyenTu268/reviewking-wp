<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_cornerstone', 10, 1 );
add_action( 'wpmc_scan_post', 'wpmc_scan_post_cornerstone', 10, 2 );
add_action( 'wpmc_scan_once', 'wpmc_scan_once_cornerstone' );

function wpmc_scan_once_cornerstone() {
	global $wpmc;

	// Fonts
	$fonts = get_option( 'cornerstone_font_config' );
	$fonts = wp_unslash( $fonts );
	$font_urls = $wpmc->get_urls_from_string( $fonts );
	$wpmc->add_reference_url( $font_urls, 'Cornerstone Fonts' );

	// Socials
	$img = get_option( 'x_social_fallback_image' );
	$img = substr( $img, 0, strpos( $img, ":" ) );
	if ( is_numeric( $img ) ) {
		$wpmc->add_reference_id( $img, 'Cornerstone Social' );
	}

}

function wpmc_scan_post_cornerstone( $content, $id ) {
	
	$post_type = get_post_type( $id );
	if ( !str_starts_with( $post_type, 'cs_' ) ) return;

	wpmc_scan_parse_cornerstone_data( $content, $id );
}

function wpmc_scan_postmeta_cornerstone( $id ) {
	global $wpmc;
	
	$meta_data = get_post_meta( $id, '_cornerstone_data', true );
	wpmc_scan_parse_cornerstone_data( $meta_data, $id );
	
	// We can simply get the URLs used in generated CSS with regex on string
	$css_data = get_post_meta($id, '_cs_generated_tss', true);
	$css_data_urls = $wpmc->get_urls_from_string( $css_data );

	$wpmc->add_reference_url( $css_data_urls, 'Cornerstone CSS', $id );
}

function wpmc_scan_parse_cornerstone_data( $content_data, $id ) {
	global $wpmc;

	$postmeta_images_ids = array();
	$postmeta_images_urls = array();

	if ( !empty( $content_data ) ) {
		$data = is_array( $content_data ) ? $content_data : json_decode( $content_data );

		// Go through the keys that uses IDs references
		$keys = [ 'bg_lower_image', 'bg_lower_img_src', 'image_src', 'mejs_source_files', 'mejs_poster', 'front_image', 'lightbox_image', 'image_after', 'image_before', 'bg_video_poster', 'bg_image', 'bild'];
		foreach ( $data as $piece ) {
			$results = [];
			$wpmc->get_from_meta( $piece, $keys, $results, $results, true );
			if ( !empty( $results ) ) {
				foreach ( $results as $result ) {
					// The keys have both the ID and size like "12:full" so keep just the ID.
					$result = substr( $result, 0, strpos( $result, ":" ) );
					if ( is_numeric( $result ) ) {
						array_push( $postmeta_images_ids, (int)$result );
					}
				}
			}
		}
		

		// Parse all URLs references
		foreach ( $data as $piece ) { 
			$to_string = json_encode( $piece );
			$urls = $wpmc->get_urls_from_string( $to_string );

			$postmeta_images_urls = array_merge( $postmeta_images_urls, $urls );
		}

		$wpmc->add_reference_url( $postmeta_images_urls, 'Cornerstone', $id );
		$wpmc->add_reference_id( $postmeta_images_ids, 'Cornerstone', $id );
	}
}

?>