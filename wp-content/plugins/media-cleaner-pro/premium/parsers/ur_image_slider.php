<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_ur_image_slider', 10, 1 );

function wpmc_scan_postmeta_ur_image_slider( $id ) {
	$type = get_post_type( $id );
  if ( $type !== 'ris_gallery' ) {
    return;
  }
  // get metadata
	global $wpmc;
  $postmeta_images_ids = [];
  $entries = get_post_meta( $id, 'ris_all_photos_details', true );
  if ( !empty( $entries ) ) {
    foreach ( $entries as $entry ) {
      $postmeta_images_ids[] = (int)$entry['rpgp_image_id'];
    }
  }
	$wpmc->add_reference_id( $postmeta_images_ids, 'UR IMAGE SLIDER (ID)' );
}

?>
