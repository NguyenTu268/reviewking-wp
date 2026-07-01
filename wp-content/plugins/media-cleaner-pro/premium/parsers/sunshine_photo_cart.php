<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_sunshine_cart', 10, 1 );

function wpmc_scan_postmeta_sunshine_cart( $id ) {
	$type = get_post_type( $id );
  if ( $type !== 'sunshine-gallery' ) {
    return;
  }
	global $wpmc;
  $postmeta_images_ids = [];
  $entries = get_attached_media( 'image', $id );
  if ( !empty( $entries ) ) {
    foreach ( $entries as $entry ) {
      $postmeta_images_ids[] = $entry->ID;
    }
  }
	$wpmc->add_reference_id( $postmeta_images_ids, 'SUNSHINE PHOTO CART (ID)' );
}

?>