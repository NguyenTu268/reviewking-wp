<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_photo_gallery', 10, 1 );

function wpmc_scan_postmeta_photo_gallery( $id ) {
	$type = get_post_type( $id );
  if ( $type !== 'bwg_gallery' ) {
    return;
  }

	global $wpmc, $wpdb;
  $paths = [];
  $post = get_post( $id );
  $codes = $wpmc->get_shortcode_attributes( 'Best_Wordpress_Gallery', $post );
  $tbl_image = $wpdb->prefix . 'bwg_image';
  foreach ( $codes as $code ) {
    $query = $wpdb->prepare( "SELECT image_url, thumb_url FROM $tbl_image WHERE gallery_id='%d'", (int)$code['id'] );
    $items = $wpdb->get_results( $query );
    foreach ( $items as $item ) {
      $paths[] = $item->image_url;
      $paths[] = $item->thumb_url;
    }
  }

  if ( !empty( $paths ) ) {
	  $wpmc->add_reference_url( $paths, 'PHOTO GALLERY', $id );
  }
}

?>