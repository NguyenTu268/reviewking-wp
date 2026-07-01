<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_powerpress', 10, 2 );

function wpmc_scan_postmeta_powerpress( $id ) {
  $enclosure = get_post_meta( $id, 'enclosure', true );
  if ( !empty( $enclosure ) ) {
    global $wpmc;
    $metaparts = explode( "\n", $enclosure, 4 );
    if ( !empty( $metaparts[0] ) && filter_var( $metaparts[0], FILTER_VALIDATE_URL ) ) {
      $url = $wpmc->clean_url( $metaparts[0] );
      $wpmc->add_reference_url( $url, 'POWERPRESS (URL)', $id );
    }
  }
}

?>