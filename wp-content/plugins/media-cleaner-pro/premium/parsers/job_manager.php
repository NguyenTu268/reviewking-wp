<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_job_manager', 10, 2 );

function wpmc_scan_postmeta_job_manager( $id ) {
  global $wpdb, $wpmc;

  // Very badly named meta, this Job Manager doesn't seem like a good plugin at all...
  $main_image = get_post_meta( $id, 'main_image', true );

  if ( !empty( $main_image ) ) {
    $main_image = explode( ',', $main_image );
    $wpmc->add_reference_id( $main_image, 'JOB MANAGER (ID)' );
  }
  
}

?>