<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$options = [
    // API keys
    'li2_publishable_key',
    'li2_api_key',

    // Client script settings
    'li2_track_pageview',
    'li2_track_clicks',
    'li2_track_scroll',
    'li2_track_forms',
    'li2_enable_debug',
    'li2_dev_mode',
    'li2_cookie_options',

    // Conversion tracking
    'li2_track_registration',

    // WooCommerce integration
    'li2_wc_track_add_to_cart',
    'li2_wc_track_sale',
];

foreach ($options as $option) {
    delete_option($option);
}
