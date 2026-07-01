<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_lana_download_manager', 10, 2 );

function wpmc_scan_postmeta_lana_download_manager( $id ) {
  $type = get_post_type( $id );
  if ( $type !== 'lana_download' ) {
    return;
  }

  global $wpmc;
  $mediaId = get_post_meta( $id, 'lana_download_file_id', true );
  $fileUrl = get_post_meta( $id, 'lana_download_file_url', true );
  if ( !empty( $mediaId ) ) {
    $wpmc->add_reference_id( $mediaId, 'LANA DOWNLOADS MANAGER (ID)' );
  }
  if ( !empty( $fileUrl ) ) {
    $fileUrl = $wpmc->clean_url( $fileUrl );
    $wpmc->add_reference_url( $fileUrl, 'LANA DOWNLOADS MANAGER (URL)', $id );
  }
}

?>