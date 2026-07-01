<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_metabox', 10, 1 );


function wpmc_scan_postmeta_metabox( $id ) {
  global $wpmc;

  $postmeta_images_ids = array();
  $postmeta_images_urls = array();

  $fields = rwmb_get_object_fields( $id );

  foreach ( $fields as $field ) {
    $value = rwmb_get_value( $field['id'], null, (int)$id );

    // Advanced Image field
    if ( isset( $field['mime_type'] ) && $field['mime_type'] === 'image' && !empty( $value ) ) {
      foreach ( $value as $key => $image ) {
          if( !is_array( $image ) ) continue;
          $postmeta_images_ids[] = $key;

          if( !isset( $image['url'] ) ) continue;
          $postmeta_images_urls[] = $wpmc->clean_url( $image['url'] );
      }
    }

    // Image field
    if ( $field['type'] === 'image' && !empty( $value ) ) {
      foreach ( $value as $key => $image ) {
          if( !is_array( $image ) ) continue;

          $postmeta_images_ids[] = $key;
          
          if( !isset( $image['url'] ) ) continue;
          $postmeta_images_urls[] = $wpmc->clean_url( $image['url'] );
      }
    }
  }

  // Add image references to the Media Cleaner
  $wpmc->add_reference_id( $postmeta_images_ids, 'Meta Box (ID)', $id );
  $wpmc->add_reference_url( $postmeta_images_urls, 'Meta Box (URL)', $id );
}

?>