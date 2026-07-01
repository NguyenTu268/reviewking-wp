<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_cmbdirectory', 10, 1 );

function wpmc_scan_postmeta_cmbdirectory( $id ) {
  global $wpmc;
  $type = get_post_type( $id );
  if ( $type !== 'cm-business' ) {
    return;
  }
  $id = get_post_meta( $id, 'cmbd_business_gallery_id', true );
  if ( !empty( $id ) ) {
    $wpmc->add_reference_id( $id, 'CM BUSINESS DIRECTORY (ID)' );
  }
}

?>