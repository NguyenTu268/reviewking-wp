<?php
// Adding action hooks for Media Cleaner plugin
add_action('wpmc_scan_once', 'wpmc_scan_once_kadence_blocks', 10, 0);
add_action('wpmc_scan_post', 'wpmc_scan_html_kadence_blocks', 10, 2);
add_action('wpmc_scan_postmeta', 'wpmc_scan_postmeta_kadence_blocks', 10, 2);

/**
 * Runs once at the beginning of the scan.
 * Can be used to check images usage in general settings, in a theme, like a favicon, etc.
 */
function wpmc_scan_once_kadence_blocks()
{
    //global $wpmc;
    // Implement your logic here
}

/**
 * Runs for each postmeta of any post type.
 * Scans and collects image IDs and URLs from post meta.
 *
 * @param int $id The post ID.
 */
function wpmc_scan_postmeta_kadence_blocks($id)
{
    // global $wpmc;
    // $postmeta_images_ids = array();
    // $postmeta_images_urls = array();

    // // Fetch data from post meta with key '_fusion'
    // $data = get_post_meta($id, '_fusion');
    // $attributes = ['id', 'url', 'thumbnail'];

    // // Get images from post meta data
    // $wpmc->get_from_meta($data, $attributes, $postmeta_images_ids, $postmeta_images_urls);

    // // Add image references to the Media Cleaner
    // $wpmc->add_reference_id($postmeta_images_ids, 'kadence_blocks (ID)', $id);
    // $wpmc->add_reference_url($postmeta_images_urls, 'kadence_blocks (URL)', $id);
}

/**
 * Runs for each post of any post type.
 * Scans and collects image URLs from the post content (HTML).
 *
 * @param string $html The post content HTML.
 * @param int $id The post ID.
 */
function wpmc_scan_html_kadence_blocks($html, $id)
{
    global $wpmc;

    $posts_images_urls = array();
    $posts_images_ids = array();

    $wpmc->get_from_blocks(
        $html,
        'kadence/',
        ['bgImg', 'bgImgID', 'lightUrl', 'url', 'thumbUrl'],
        $posts_images_urls,
        $posts_images_ids
    );


    $wpmc->add_reference_url($posts_images_urls, 'Kadence Blocks (URL)', $id);
    $wpmc->add_reference_id($posts_images_ids, 'Kadence Blocks (ID)', $id);
}