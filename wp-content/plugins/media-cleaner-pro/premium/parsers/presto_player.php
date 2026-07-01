<?php

add_action( 'wpmc_scan_post', 'wpmc_scan_post_presto_player', 10, 2 );

function wpmc_scan_post_presto_player( $html, $id ) {
	$type = get_post_type( $id );
  if ( $type !== 'pp_video_block' ) {
    return;
  }

	global $wpmc;
  $postmeta_images_ids = [];
  $postmeta_images_urls = [];

  $data = parse_blocks( $html );
  $wpmc->get_from_meta( $data, array( 'src', 'attachment_id' ),
			$postmeta_images_ids, $postmeta_images_urls );

	$wpmc->add_reference_id( $postmeta_images_ids, 'PRESTO PLAYER (ID)', $id );
  $wpmc->add_reference_url( $postmeta_images_urls, 'PRESTO PLAYER (URL)', $id );
}

?>
