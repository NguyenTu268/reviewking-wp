<?

add_action('wpmc_scan_once', 'wpmc_scan_once_tutor_lms', 10, 0);
add_action('wpmc_scan_post', 'wpmc_scan_html_tutor_lms', 10, 2);


function wpmc_scan_once_tutor_lms()
{
    global $wpdb, $wpmc;

    $postmeta_images_urls = array();

    // Get the _thumbnail_id meta values for posts of type 'courses', 'lesson', or 'quiz'
    $postmeta_images_ids = $wpdb->get_col(
        "
        SELECT DISTINCT pm.meta_value
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = '_thumbnail_id'
        AND p.post_type IN ('courses', 'lesson', 'quiz')
        "
    );

    $wpmc->add_reference_id( $postmeta_images_ids, 'Tutor LMS ( ID )' );

    // Tutor LMS uses the media full size URL by default in thumbnails, so let's add them to the reference
    $postmeta_images_urls = array_map( function( $id ) use ( $wpmc ) {
        $url = wp_get_attachment_url( $id );
        $url = $wpmc->clean_url( $url );

        return $url;
    }, $postmeta_images_ids );

    $wpmc->add_reference_url( $postmeta_images_urls, 'Tutor LMS ( URL )' );
}

function wpmc_scan_html_tutor_lms( $html, $id )
{

    global $wpmc;

    $post_types = array( 'courses', 'lesson', 'quiz' );
    $post_type = get_post_type( $id );

    if ( ! in_array( $post_type, $post_types ) ) {
        return;
    }
    
    // Skip the encoding since we receive the HTML as a string
    $urls = $wpmc->get_urls_from_html( $html );

    array_map( function( $url ) use ( $wpmc ) {
        $url = $wpmc->clean_url( $url );
        return $url;
    }, $urls );


    $wpmc->add_reference_url( $urls, 'Tutor LMS ( URL )', $id );
}


?>