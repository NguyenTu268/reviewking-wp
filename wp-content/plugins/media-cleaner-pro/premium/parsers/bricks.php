<?php


add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_bricks', 10, 2 );
add_filter( 'wpmc_affect_html_from_builder', 'wpmc_affect_html_from_builder_bricks', 10, 2 );

  /**
   * Bricks Builder does not expose its content in the post content,
   * so for other parsers that checks the post content, we need to affect the HTML before scanning it.
   */
  function wpmc_affect_html_from_builder_bricks( $html, $id ) {
    $content = get_post_meta( $id, '_bricks_page_content_2' );
    $string_content = json_encode( $content );
    $html .= $string_content; // Append the Bricks content to the HTML for scanning
  
    return $html;
  }

  /**
   * Runs for each postmeta of any post type.
   * Scans and collects image IDs and URLs from post meta.
   *
   * @param int $id The post ID.
   */
  function wpmc_scan_postmeta_bricks( $id ) {
    global $wpmc;
    $postmeta_images_ids = array();
    $postmeta_images_urls = array();
  
    // Fetch data from post meta with key '_bricks_page_content_2'
    $data = get_post_meta( $id, '_bricks_page_content_2' );
    $attributes = [ 'url', 'id' ];
    
    // Get images from post meta data
    $wpmc->get_from_meta( $data, $attributes, $postmeta_images_ids, $postmeta_images_urls );
  
    // Add image references to the Media Cleaner
    $wpmc->add_reference_id( $postmeta_images_ids, 'Bricks (ID)', $id );
    $wpmc->add_reference_url( $postmeta_images_urls, 'Bricks (URL)', $id );
  }
  

?>