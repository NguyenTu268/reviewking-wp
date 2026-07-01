<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_enfold', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_avia_framework', 10, 2 );

function wpmc_scan_once_enfold() {
  global $wpmc;

  $avia_options_enfold = get_option( 'avia_options_enfold', '' );
  if ( !empty( $avia_options_enfold ) ) {
    $postmeta_images_ids = array();
	  $postmeta_images_urls = array();
    $wpmc->get_from_meta( $avia_options_enfold, array( 'logo', 'preloader_logo' ), $postmeta_images_ids, $postmeta_images_urls );
    $wpmc->add_reference_id( $postmeta_images_ids, 'ENFOLD THEME' );
	  $wpmc->add_reference_url( $postmeta_images_urls, 'ENFOLD THEME' );
  }
}	

function wpmc_scan_html_avia_framework( $html, $id ) {
	global $wpmc;

	$ids = [];
	$urls = [];

	// All the content might not be in shortcodes, so let's parse any "href" and "src" attributes too
	$types = $wpmc->types;
	$attr = "href|src";
	preg_match_all( "/(?:$attr)=[\"']((https?:\/\/)?[^\\&\#\[\] \"\?]+[.]($types))[\"']/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) ) {
		foreach ( $res[1] as $url ) {
			array_push( $urls, $wpmc->clean_url( $url ) );
		}
	}

	$nodes = $wpmc->nested_shortcodes_to_array( $html );
	$wpmc->array_to_ids_or_urls( $nodes, $ids, $urls, true, [ "image", "id", "ids", "src", "url", "attachment" ] );

	$urls = array_unique( $urls );
	$ids  = array_unique( $ids );

	foreach ( $urls as $url ) {
		$wpmc->add_reference_url( $url, 'ENFOLD', $id );

		$srcset_urls = $wpmc->get_thumbnails_urls_from_srcset( $url );
		$srcset_urls = array_unique( $srcset_urls );

		$wpmc->add_reference_url( $srcset_urls, 'ENFOLD {SAFE}', $id );
	}

	
	$wpmc->add_reference_id( $ids, 'ENFOLD', $id );

}

?>