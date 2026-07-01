<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_wp_discuz', 10, 1 );

function wpmc_scan_postmeta_wp_discuz( $id ) {
  global $wpmc;
  $args = array( 'post_id' => $id, 'meta_key' => 'wmu_attachments', 'fields' => 'ids' );
  $comments = get_comments( $args );
  foreach ( $comments as $commentId ) {
    $data = get_comment_meta( $commentId, 'wmu_attachments', true );
    if ( isset( $data['images'] ) && !empty( $data['images'] ) ) {
      $wpmc->add_reference_id( $data['images'], 'WP DISCUZ (ID)' );
    }
  }
}

?>