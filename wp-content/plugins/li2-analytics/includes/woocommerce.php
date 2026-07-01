<?php
if (!defined('ABSPATH')) {
    exit;
}

/* =========================================================
   WOOCOMMERCE ORDER TRACKING
========================================================= */

add_action('plugins_loaded', 'li2_init_tracking');

function li2_init_tracking() {

    // Registration tracking (no WooCommerce dependency)
    if (get_option('li2_track_registration', 1)) {
        add_action('user_register', 'li2_send_registration');
    }

    if (!class_exists('WooCommerce')) {
        return;
    }

    if (get_option('li2_wc_track_sale', 1)) {
        add_action('woocommerce_new_order', 'li2_send_order');
    }

    if (get_option('li2_wc_track_add_to_cart', 1)) {
        add_action('woocommerce_add_to_cart', 'li2_send_add_to_cart', 10, 6);
    }
}

function li2_send_registration($user_id) {

    $api_key = get_option('li2_api_key');

    if (!$api_key) {
        return;
    }

    $user = get_userdata($user_id);

    if (!$user) {
        return;
    }

    $click_id = isset($_COOKIE['li_cid'])
        ? sanitize_text_field(wp_unslash($_COOKIE['li_cid']))
        : null;

    $payload = [
        'event_name'  => 'lead',
        'external_id' => (string) $user_id,
        'email'       => $user->user_email ?: null,
        'name'        => trim($user->display_name) ?: null,
        'metadata'    => [
            'action' => 'registration',
        ],
    ];

    if ($click_id) {
        $payload['click_id'] = $click_id;
    }

    wp_remote_post(
        li2_get_api_base_url() . '/track/lead',
        [
            'headers' => [
                'Content-Type'  => 'application/json',
                'X-Li2-API-Key' => $api_key,
            ],
            'body'    => wp_json_encode($payload),
            'timeout' => 5,
        ]
    );
}

/**
 * Zero-decimal currencies — amounts are already whole units, no * 100 needed.
 * Reference: https://stripe.com/docs/currencies#zero-decimal
 */
function li2_to_cents($amount, $currency) {
    $zero_decimal = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW',
                     'MGA', 'PYG', 'RWF', 'UGX', 'UYI', 'VND', 'VUV', 'XAF',
                     'XOF', 'XPF'];
    if (in_array(strtoupper($currency), $zero_decimal, true)) {
        return (int) round($amount);
    }
    return (int) round($amount * 100);
}

function li2_send_order($order_id) {

    $api_key = get_option('li2_api_key');

    if (!$api_key) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    /* ---------- clickId ---------- */

    $click_id = isset($_COOKIE['li_cid'])
        ? sanitize_text_field(wp_unslash($_COOKIE['li_cid']))
        : null;

    /* ---------- products ---------- */

    $products = [];

    foreach ($order->get_items() as $item) {

        $product = $item->get_product();

        $products[] = [
            'product_id' => $product ? $product->get_id() : null,
            'name'       => $item->get_name(),
            'quantity'   => $item->get_quantity(),
            'price'      => $order->get_item_total($item)
        ];
    }

    /* ---------- currency ---------- */

    $currency = $order->get_currency();

    if (!in_array($currency, ['USD', 'VND'])) {
        $currency = 'USD';
    }

    $customer_id = (string) $order->get_customer_id();
    $email = $order->get_billing_email();
    
    // For guest checkouts, use their email as the external identification
    if ($customer_id === '0' || empty($customer_id)) {
        $customer_id = $email;
    }

    /* ---------- identify ---------- */
    // Trigger an identify event if we have a click_id and a customer_id
    if ($click_id && $customer_id) {
        $identify_payload = [
            'event_name'  => '__identify__',
            'external_id' => $customer_id,
            'email'       => $email,
            'phone'       => $order->get_billing_phone(),
            'name'        => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            'click_id'    => $click_id,
            'metadata'    => [
                'type' => 'identify'
            ]
        ];

        wp_remote_post(
            li2_get_api_base_url() . '/track/lead',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Li2-API-Key' => $api_key
                ],
                'body'    => wp_json_encode($identify_payload),
                'timeout' => 5
            ]
        );
    }

    /* ---------- payload ---------- */

    $payload = [
        'event_name'  => 'sale',
        'external_id' => $customer_id,
        'name'        => trim(
            $order->get_billing_first_name() . ' ' .
            $order->get_billing_last_name()
        ),
        'email'       => $email,
        'phone'       => $order->get_billing_phone(),
        'amount'      => li2_to_cents((float) $order->get_total(), $currency),
        'currency'    => $currency,
        'metadata'    => [
            'order_id' => $order_id,
            'total'    => $order->get_total(),
            'products' => $products
        ]
    ];

    if ($click_id) {
        $payload['click_id'] = $click_id;
    }

    /* ---------- send request ---------- */

    $response = wp_remote_post(
        li2_get_api_base_url() . '/track/sale',
        [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Li2-API-Key' => $api_key
            ],
            'body'    => wp_json_encode($payload),
            'timeout' => 10
        ]
    );

    /* ---------- logging ---------- */

    if (class_exists('WC_Logger')) {

        $logger = wc_get_logger();

        $logger->info(
            'LI2 API response: ' . wp_json_encode($response),
            ['source' => 'li2']
        );
    }
}

function li2_send_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {

    $api_key = get_option('li2_api_key');

    if (!$api_key) {
        return;
    }

    $product = wc_get_product($variation_id ? $variation_id : $product_id);

    if (!$product) {
        return;
    }

    /* ---------- clickId ---------- */

    $click_id = isset($_COOKIE['li_cid'])
        ? sanitize_text_field(wp_unslash($_COOKIE['li_cid']))
        : null;

    /* ---------- customer data ---------- */

    $user_id = get_current_user_id();
    $customer_id = $user_id ? (string) $user_id : null;
    $email = null;
    $phone = null;
    $name = null;

    if ($user_id) {
        $customer = new WC_Customer($user_id);
        $email = $customer->get_billing_email();
        if (empty($email)) {
            $wp_user = get_userdata($user_id);
            $email = $wp_user ? $wp_user->user_email : null;
        }
        $phone = $customer->get_billing_phone();
        $name = trim($customer->get_billing_first_name() . ' ' . $customer->get_billing_last_name());
    }

    /* ---------- payload ---------- */

    $payload = [
        'event_name'  => 'lead',
        'external_id' => $customer_id,
        'email'       => $email,
        'phone'       => $phone,
        'name'        => $name ? trim($name) : null,
        'metadata'    => [
            'action'     => 'add_to_cart',
            'product_id' => $product->get_id(),
            'name'       => $product->get_name(),
            'quantity'   => $quantity,
            'price'      => $product->get_price()
        ]
    ];

    if ($click_id) {
        $payload['click_id'] = $click_id;
    }

    /* ---------- send request ---------- */

    $response = wp_remote_post(
        li2_get_api_base_url() . '/track/lead',
        [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Li2-API-Key' => $api_key
            ],
            'body'    => wp_json_encode($payload),
            'timeout' => 10
        ]
    );

    /* ---------- logging ---------- */

    if (class_exists('WC_Logger')) {

        $logger = wc_get_logger();

        $logger->info(
            'LI2 API add_to_cart response: ' . wp_json_encode($response),
            ['source' => 'li2']
        );
    }
}
