<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_woodmart' );

function wpmc_scan_once_woodmart() {
	global $wpdb, $wpmc;
	$query = "SELECT meta_value FROM {$wpdb->termmeta} WHERE meta_key = \"title_image\" AND meta_value <> \"\"";
	$metas = $wpdb->get_col( $query );
	if ( count( $metas ) > 0 ) {
		$postmeta_images_urls = [];
		foreach ( $metas as $meta ) {
      $meta = $wpmc->clean_url( $meta );
      if ( !empty( $meta ) ) {
        $postmeta_images_urls[] = $meta;
      }
		}
		$wpmc->add_reference_url( $postmeta_images_urls, 'WOODMART (URL)' );
	}
}


?>