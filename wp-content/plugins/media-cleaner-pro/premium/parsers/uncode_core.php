<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_uncode', 10, 1 );

function wpmc_scan_postmeta_uncode( $id ) {
	global $wpmc;
  
  $data = get_post_meta( $id, '_uncode_featured_media', true );
  if ( !empty( $data ) ) {
    $postmeta_images_ids = array();
    $postmeta_images_ids = explode( ',', $data );
    if ( !empty( $postmeta_images_ids ) ) {
      $wpmc->add_reference_id( $postmeta_images_ids, 'UNCODE FEATURED MEDIA (ID)', $id );
    }
  }
}

?>