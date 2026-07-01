<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_download_monitor', 10, 2 );

function wpmc_scan_postmeta_download_monitor( $id ) {
  $type = get_post_type( $id );
  if ( $type !== 'dlm_download' ) {
    return;
  }

  global $wpmc;
  // try {
  $versions = get_posts( 'post_parent=' . $id . '&post_type=dlm_download_version&fields=ids&post_status=publish&numberposts=-1' );
  foreach ( $versions as $version ) {
    $files = get_post_meta( $version, '_files', true );
    if ( !empty( $files ) ) {
      $files = json_decode( $files );
      foreach ( $files as $file ) {
        $file = $wpmc->clean_url( $file );
        $wpmc->add_reference_url( $file, 'DOWNLOAD MANAGER (URL)', $id );
      }
    }
  }
  // }
  // catch ( Exception $exception ) {
  //   error_log( $exception->message );
  // }
}

?>