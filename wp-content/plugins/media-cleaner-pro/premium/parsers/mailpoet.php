<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_mailpoet', 10, 0 );

function wpmc_parse_mailpoet_blocks( $blocks, &$images ) {
  foreach ( $blocks as $block ) {
    if ( $block['type'] == 'image' ) {
      $images[] = $block['src'];
    }
    if ( isset( $block['blocks'] ) ) {
      wpmc_parse_mailpoet_blocks( $block['blocks'], $images );
    }
  }
}

function wpmc_scan_once_mailpoet() {
	global $wpdb, $wpmc;
	$table = $wpdb->prefix . 'mailpoet_newsletters';
	$results = $wpdb->get_results( "SELECT id, body FROM $table" );
  $ids = array();
	$bodies = array();
	foreach ( $results as $result ) {
    $ids[] = $result->id;
    $bodies[] = $result->body;
	}
  $ids = [];
  $urls = [];
  foreach ( $bodies as $body ) {
    $data = json_decode($body, true);
    $urls = [];
    $images = [];
    wpmc_parse_mailpoet_blocks( $data['content']['blocks'], $images );
    foreach ( $images as $image ) {
      $clean_url = $wpmc->clean_url( $image );
      if ( !empty( $clean_url ) ) {
        $urls[] = $clean_url;
      }
    }
    $wpmc->add_reference_url( $urls, 'MAILPOET (URL)' );
  }
}
