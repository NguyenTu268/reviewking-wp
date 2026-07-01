<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_social_warfare', 10, 2 );

function wpmc_scan_postmeta_social_warfare( $id ) {
  global $wpdb, $wpmc;

  // Pinterest URL
  $swp_pinterest_image_url = get_post_meta( $id, 'swp_pinterest_image_url', true );
  if ( !empty( $swp_pinterest_image_url ) ) {
    $swp_pinterest_image_url = $wpmc->clean_url( $swp_pinterest_image_url );
    $wpmc->add_reference_url( $swp_pinterest_image_url, 'SOCIAL WARFARE (URL)', $id );
  }
  
  // OpenGraph URL
  $swp_og_image_url = get_post_meta( $id, 'swp_og_image_url', true );
  if ( !empty( $swp_og_image_url ) ) {
    $swp_og_image_url = $wpmc->clean_url( $swp_og_image_url );
    $wpmc->add_reference_url( $swp_og_image_url, 'SOCIAL WARFARE (URL)', $id );
  }

  // Pinterest ID
  $swp_pinterest_image = get_post_meta( $id, 'swp_pinterest_image', true );
  if ( !empty( $swp_pinterest_image ) ) {
    $wpmc->add_reference_id( $swp_pinterest_image, 'SOCIAL WARFARE (URL)', $id );
  }
  
  // OpenGraph ID
  $swp_og_image = get_post_meta( $id, 'swp_og_image', true );
  if ( !empty( $swp_og_image ) ) {
    $wpmc->add_reference_id( $swp_og_image, 'SOCIAL WARFARE (ID)', $id );
  }

  // Twitter ID
  $swp_twitter_card_image = get_post_meta( $id, 'swp_twitter_card_image', true );
  if ( !empty( $swp_twitter_card_image ) ) {
    $wpmc->add_reference_id( $swp_twitter_card_image, 'SOCIAL WARFARE (ID)', $id );
  }
}

?>