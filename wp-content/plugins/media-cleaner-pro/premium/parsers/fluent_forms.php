<?php
// Adding action hooks for Media Cleaner plugin
add_action('wpmc_scan_once', 'wpmc_scan_once_fluent_form', 10, 0);
add_action('wpmc_scan_post', 'wpmc_scan_html_fluent_form', 10, 2);
add_action('wpmc_scan_postmeta', 'wpmc_scan_postmeta_fluent_form', 10, 2);

/**
 * Runs once at the beginning of the scan.
 * Can be used to check images usage in general settings, in a theme, like a favicon, etc.
 */
function wpmc_scan_once_fluent_form()
{
    global $wpdb;
    global $wpmc;

    // get all form_fields from the fluentform_forms table where status is published
    $table_name = $wpdb->prefix . 'fluentform_forms';
    $forms = $wpdb->get_results("SELECT id, form_fields FROM $table_name WHERE status = 'published'");

    $postmeta_images_ids = array();
    $postmeta_images_urls = array();

    foreach( $forms as $form ) {
        $form_fields = json_decode( $form->form_fields, true);

        if ( !is_array( $form_fields ) ) {
            continue;
        }

        $wpmc->array_to_ids_or_urls(
            $form_fields,
            $postmeta_images_ids,
            $postmeta_images_urls,
            true, // Recursive
            [
                'image',
                'media',
            ]
        );


       
    }

     // Add references to Media Cleaner
    $wpmc->add_reference_id($postmeta_images_ids, 'FLUENT FORMS (ID)');
    $wpmc->add_reference_url($postmeta_images_urls, 'FLUENT FORMS (URL)');


}

/**
 * Runs for each postmeta of any post type.
 * Scans and collects image IDs and URLs from post meta.
 *
 * @param int $id The post ID.
 */
function wpmc_scan_postmeta_fluent_form($id)
{

    
}

/**
 * Runs for each post of any post type.
 * Scans and collects image URLs from the post content (HTML).
 *
 * @param string $html The post content HTML.
 * @param int $id The post ID.
 */
function wpmc_scan_html_fluent_form($html, $id)
{

}