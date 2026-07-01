<?php
add_action('wpmc_scan_post', 'wpmc_scan_html_spectra', 10, 2);


function wpmc_scan_html_spectra($html, $id)
{
    global $wpmc;

    $posts_images_urls = array();
    $posts_images_ids = array();

    $wpmc->get_from_blocks(
        $html,
        'uagb/',
        ['id', 'url', 'urlTablet', 'urlMobile'],
        $posts_images_urls,
        $posts_images_ids
    );


    $wpmc->add_reference_url( $posts_images_urls, 'Spectra (URL)', $id );
    $wpmc->add_reference_id( $posts_images_ids, 'Spectra (ID)', $id );
}

?>