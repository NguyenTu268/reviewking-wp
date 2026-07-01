<?php

add_action('wpmc_scan_once', 'wpmc_scan_once_smartslider3', 10, 0 );

function wpmc_scan_once_smartslider3() {
  global $wpdb;
  global $wpmc;

  $table_slides = $wpdb->prefix . "nextend2_smartslider3_slides";
  
  $descs = $wpdb->get_col( "SELECT params FROM $table_slides" );
  foreach ( $descs as $desc ) {
    preg_match_all('#\$upload\$[^,\s()]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $desc, $match);
    $urls = str_replace('$upload$/', '', str_replace("\\/",'/',$match[0]));
    $wpmc->add_reference_url( $urls, 'SMART SLIDER 3 (URL)' );
  }

  $descs = $wpdb->get_col( "SELECT slide FROM $table_slides" );
  foreach ( $descs as $desc ) {
    preg_match_all('#\$upload\$[^,\s()]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $desc, $match);
    $urls = str_replace('$upload$/', '', $match[0]);
    $wpmc->add_reference_url( $urls, 'SMART SLIDER 3 (URL)' );
  }
}

?>