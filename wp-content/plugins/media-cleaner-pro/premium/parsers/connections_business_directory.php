<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_connections_business_directory', 10, 0 );

function wpmc_scan_once_connections_business_directory() {
	global $wpdb, $wpmc;
	$table = $wpdb->prefix . 'connections';
	$results = $wpdb->get_results( "SELECT options, notes, bio FROM $table" );
	$options = array();
	$notes = array();
	$bios = array();
	foreach ( $results as $result ) {
		$options[] = $result->options;
		$notes[] = $result->notes;
		$bios[] = $result->bio;
	}

	// Check options
	$options = array_map( 'json_decode', $options );
	foreach ( $options as $option ) {
		$urls = array();
		if ( !empty( $option->logo->meta->url ) ) {
			$urls[] = $wpmc->clean_url( $option->logo->meta->url );
		}
		if ( !empty( $option->image->meta->original->url ) ) {
			$urls[] = $wpmc->clean_url( $option->image->meta->original->url );
		}
		$wpmc->add_reference_url( $urls, 'CONNECTIONS BUSINESS DIRECTORY (URL)' );
	}

	// Check notes
	$notesUrls = [];
	foreach ( $notes as $note ) {
		$notesUrls = $wpmc->get_urls_from_html( $note );
		$wpmc->add_reference_url( $notesUrls, 'CONNECTIONS BUSINESS DIRECTORY (URL)' );
	}

	// Check bios
	$bioUrls = [];
	foreach ( $bios as $bio ) {
		$bioUrls = $wpmc->get_urls_from_html( $bio );
		$wpmc->add_reference_url( $bioUrls, 'CONNECTIONS BUSINESS DIRECTORY (URL)' );
	}
}

?>