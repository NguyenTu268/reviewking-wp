<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_web_directory', 10, 2 );

function wpmc_scan_postmeta_web_directory( $id ) {
  $type = get_post_type( $id );
  if ( $type !== 'w2dc_listing' ) {
    return;
  }

  global $wpmc;
  $ids = get_post_meta( $id, '_attached_image', false );
  if ( !empty( $ids ) ) {
    $wpmc->add_reference_id( $ids, 'WEB DIRECTORY (ID)' );
  }
}

?>