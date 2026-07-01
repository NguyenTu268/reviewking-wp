<?php

// POST TYPES: academy_courses

// TABLE: academy_lessons, let's look in the lesson_content

// TABLE academy_lessonmeta, let's look at the meta_value for meta_key = featured_media, or attachment.

add_action( 'wpmc_scan_once', 'wpmc_scan_once_academy_lms', 10, 0 );

function wpmc_scan_once_academy_lms() {
  global $wpdb, $wpmc;
  $table = $wpdb->prefix . 'academy_lessons';
  $results = $wpdb->get_results( "SELECT lesson_content FROM $table" );
  $lesson_content = array();
  foreach ( $results as $result ) {
    $lesson_content[] = $result->lesson_content;
  }

  // Check lesson_content
  $urls = $wpmc->get_urls_from_html( $lesson_content );
  if ( !empty( $urls ) ) {
    $wpmc->add_reference_url( $urls, 'ACADEMY LMS (URL)' );
  }

  // Check academy_lessonmeta
  $table = $wpdb->prefix . 'academy_lessonmeta';
  $results = $wpdb->get_results( "SELECT meta_value FROM $table WHERE meta_key = 'featured_media' OR meta_key = 'attachment'" );
  foreach ( $results as $result ) {
    $value = $result->meta_value;
    if ( is_numeric( $value ) ) {
      $wpmc->add_reference_id( (int)$value, 'ACADEMY LMS (ID)' );
    }
    else if ( is_string( $value ) ) {
      $wpmc->add_reference_url( $value, 'ACADEMY LMS (URL)' );
    }
  }
}