<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_wonderplugin_3dcarousel', 10, 0 );

function wpmc_scan_once_wonderplugin_3dcarousel() {
	global $wpdb, $wpmc;
	$table = $wpdb->prefix . 'wonderplugin_3dcarousel';
	$datas = $wpdb->get_col( "SELECT data FROM $table" );
	$datas = array_map( 'json_decode', $datas );
	foreach ( $datas as $data ) {
		$urls = array();
		foreach ( $data->slides as $slide ) {
			if ( !empty( $slide->image ) ) {
				$urls[] = $wpmc->clean_url( $slide->image );
			}
			if ( !empty( $slide->thumbnail ) ) {
				$urls[] = $wpmc->clean_url( $slide->thumbnail );
			}
			if ( !empty( $slide->mp3 ) ) {
				$urls[] = $wpmc->clean_url( $slide->mp3 );
			}
			if ( !empty( $slide->mp4 ) ) {
				$urls[] = $wpmc->clean_url( $slide->mp4 );
			}
			if ( !empty( $slide->video ) ) {
				$urls[] = $wpmc->clean_url( $slide->video );
			}
		}
		$wpmc->add_reference_url( $urls, '3D CAROUSEL (URL)' );
	}
}

?>