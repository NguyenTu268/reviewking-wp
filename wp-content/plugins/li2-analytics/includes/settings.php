<?php
if (!defined('ABSPATH')) {
    exit;
}

/* =========================================================
   SETTINGS PAGE
========================================================= */

add_action('admin_menu', 'li2_add_settings_page');
add_action('admin_init', 'li2_register_settings');
add_action('admin_init', 'li2_handle_push_configuration');
add_action('admin_init', 'li2_save_settings');

/**
 * Handle push configuration via URL parameters.
 *
 * When the settings page is loaded with ?push_configuration=true,
 * read publishable_key, api_key, clicks, scroll, pageviews from the
 * query string and save any valid values into the WordPress options.
 */
function li2_handle_push_configuration() {
    // Only act on our settings page
    if (!isset($_GET['page']) || sanitize_text_field(wp_unslash($_GET['page'])) !== 'li2-analytics') {
        return;
    }

    if (!isset($_GET['push_configuration']) || sanitize_text_field(wp_unslash($_GET['push_configuration'])) !== 'true') {
        return;
    }

    // Permission check
    if (!current_user_can('manage_options')) {
        return;
    }

    $updated = [];

    // --- Keys (string values) ---
    if (isset($_GET['publishable_key'])) {
        $pub_key = sanitize_text_field(wp_unslash($_GET['publishable_key']));
        if (!empty($pub_key)) {
            update_option('li2_publishable_key', $pub_key);
            $updated[] = 'Publishable Key';
        }
    }

    if (isset($_GET['server_api_key'])) {
        $api_key = sanitize_text_field(wp_unslash($_GET['server_api_key']));
        if (!empty($api_key)) {
            update_option('li2_api_key', $api_key);
            $updated[] = 'Server API Key';
        }
    }

    // --- Toggle values (boolean-style: "true"/"false" or "1"/"0") ---
    $toggles = [
        // 'clicks'           => 'li2_track_clicks',    // remote config
        // 'scroll'           => 'li2_track_scroll',    // remote config
        // 'pageviews'        => 'li2_track_pageview',  // remote config
        // 'forms'            => 'li2_track_forms',     // remote config
        'track_registration'  => 'li2_track_registration',
        'track_add_to_cart'   => 'li2_wc_track_add_to_cart',
        'track_checkout'      => 'li2_wc_track_sale',
        'dev_mode'            => 'li2_dev_mode',
    ];

    foreach ($toggles as $param => $option_name) {
        if (isset($_GET[$param])) {
            $raw = sanitize_text_field(wp_unslash($_GET[$param]));
            // Accept "true"/"1" as enabled, "false"/"0" as disabled
            if (in_array($raw, ['true', '1', 'false', '0'], true)) {
                $value = ($raw === 'true' || $raw === '1') ? 1 : 0;
                update_option($option_name, $value);
                $updated[] = ucfirst($param);
            }
        }
    }

    if (!empty($updated)) {
        add_action('admin_notices', function () use ($updated) {
            $list = implode(', ', $updated);
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo esc_html("Configuration pushed successfully: {$list}.");
            echo '</p></div>';
            ?>
            <script>
                if (window.opener) {
                    window.opener.postMessage({
                        type: 'li2_analytics_config_pushed',
                        success: true,
                        message: 'Configuration pushed successfully',
                        updated: <?php echo json_encode($updated); ?>
                    }, '*');
                }
            </script>
            <?php
        });
    }
}

function li2_save_settings() {
    if (!isset($_POST['li2_save_settings_nonce'])) {
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    check_admin_referer('li2_save_settings_nonce', 'li2_save_settings_nonce');

    $fields = [
        'li2_publishable_key' => 'li2_validate_publishable_key',
        'li2_api_key'         => 'li2_validate_api_key',
        // 'li2_cookie_options'  => 'li2_validate_cookie_options',  // remote config
    ];

    foreach ($fields as $field => $callback) {
        $value = isset($_POST[$field]) ? wp_unslash($_POST[$field]) : '';
        update_option($field, call_user_func($callback, $value));
    }

    $checkboxes = [
        // 'li2_track_pageview', 'li2_track_clicks', 'li2_track_scroll', 'li2_track_forms',  // remote config
        'li2_enable_debug', 'li2_dev_mode',
        'li2_track_registration',
        'li2_wc_track_add_to_cart', 'li2_wc_track_sale',
    ];

    foreach ($checkboxes as $field) {
        update_option($field, isset($_POST[$field]) ? 1 : 0);
    }

    add_action('admin_notices', function () {
        echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
    });
}

function li2_add_settings_page() {
    add_menu_page(
        'LI2 Analytics',
        'LI2 Analytics',
        'manage_options',
        'li2-analytics',
        'li2_render_settings_page',
        plugin_dir_url(__DIR__) . 'assets/logo.png',
        80
    );
}
add_action('admin_head', function () {
    ?>
    <style>
        #adminmenu #toplevel_page_li2-analytics .wp-menu-image img {
            width: 20px;
            height: 20px;
        }
    </style>
    <?php
});
function li2_register_settings() {
    register_setting('li2_settings', 'li2_api_key', array(
        'type' => 'string',
        'sanitize_callback' => 'li2_validate_api_key'
    ));
    register_setting('li2_settings', 'li2_publishable_key', array(
        'type' => 'string',
        'sanitize_callback' => 'li2_validate_publishable_key'
    ));

    // Client Script Settings
    register_setting('li2_settings', 'li2_dev_mode', array(
        'type' => 'boolean',
        'sanitize_callback' => 'absint',
        'default' => 0
    ));
    // register_setting('li2_settings', 'li2_track_pageview', ...  // remote config
    // register_setting('li2_settings', 'li2_track_clicks', ...    // remote config
    // register_setting('li2_settings', 'li2_track_scroll', ...    // remote config
    // register_setting('li2_settings', 'li2_track_forms', ...     // remote config
    // register_setting('li2_settings', 'li2_cookie_options', ...  // remote config
    register_setting('li2_settings', 'li2_enable_debug', array(
        'type' => 'boolean',
        'sanitize_callback' => 'absint',
        'default' => 0
    ));

    // Conversion Tracking settings
    register_setting('li2_settings', 'li2_track_registration', array(
        'type' => 'boolean',
        'sanitize_callback' => 'absint',
        'default' => 1
    ));

    // WooCommerce settings
    register_setting('li2_settings', 'li2_wc_track_add_to_cart', array(
        'type' => 'boolean',
        'sanitize_callback' => 'absint',
        'default' => 1
    ));
    register_setting('li2_settings', 'li2_wc_track_sale', array(
        'type' => 'boolean',
        'sanitize_callback' => 'absint',
        'default' => 1
    ));
}

function li2_mask_key($key) {
    if (empty($key) || strlen($key) < 10) {
        return $key;
    }
    return substr($key, 0, 6) . '********' . substr($key, -4);
}

function li2_get_api_base_url() {
    $dev_mode = get_option('li2_dev_mode', 0);
    return $dev_mode ? 'https://sapi-dev.quik.vn/api/v1' : 'https://api.li2.ai/api/v1';
}

function li2_validate_api_key($api_key) {
    if (empty($api_key)) {
        return $api_key;
    }
    
    // First, sanitize the raw input
    $api_key = sanitize_text_field($api_key);

    $old_key = get_option('li2_api_key');
    if ($old_key && $api_key === li2_mask_key($old_key)) {
        // If the submitted value is exactly the masked version of the current key,
        // it means the user didn't change it. Return the real, unmasked key.
        return $old_key;
    }

    return $api_key;
}

function li2_validate_publishable_key($pub_key) {
    if (empty($pub_key)) {
        return $pub_key;
    }

    $pub_key = sanitize_text_field($pub_key);

    return $pub_key;
}

function li2_validate_cookie_options($value) {
    if (empty($value)) {
        return '';
    }

    $value = sanitize_text_field($value);

    // Must be valid JSON object or empty
    $decoded = json_decode($value, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        add_settings_error(
            'li2_cookie_options',
            'invalid_json',
            'Cookie Options must be a valid JSON object (e.g. {"domain":".example.com"}).',
            'error'
        );
        return get_option('li2_cookie_options', '');
    }

    return $value;
}

function li2_render_settings_page() {
?>
<div class="wrap">
    <h1>LI2 Analytics</h1>

    <form method="post" action="">
        <?php wp_nonce_field('li2_save_settings_nonce', 'li2_save_settings_nonce'); ?>

        <table class="form-table">
            <tr>
                <th scope="row">Publishable Key</th>
                <td>
                    <input
                        type="text"
                        name="li2_publishable_key"
                        value="<?php echo esc_attr(get_option('li2_publishable_key')); ?>"
                        style="width: 400px;"
                    />
                </td>
            </tr>

            <tr>
                <th scope="row">Server API Key</th>
                <td>
                    <input
                        type="text"
                        name="li2_api_key"
                        value="<?php echo esc_attr(li2_mask_key(get_option('li2_api_key'))); ?>"
                        style="width: 400px;"
                    />
                </td>
            </tr>
        </table>
        
        <?php /* remote config — track pageview/clicks/scroll/forms are now controlled server-side
        <h2 class="title">Client Script Settings</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Track Pageview</th>
                <td>
                    <input type="checkbox" name="li2_track_pageview" value="1" ... />
                </td>
            </tr>
            <tr>
                <th scope="row">Track Clicks</th>
                <td>
                    <input type="checkbox" name="li2_track_clicks" value="1" ... />
                </td>
            </tr>
            <tr>
                <th scope="row">Track Scroll</th>
                <td>
                    <input type="checkbox" name="li2_track_scroll" value="1" ... />
                </td>
            </tr>
            <tr>
                <th scope="row">Track Forms</th>
                <td>
                    <input type="checkbox" name="li2_track_forms" value="1" ... />
                </td>
            </tr>
        </table>
        */ ?>

        <details style="margin-top: 20px; border: 1px solid #ccd0d4; padding: 10px; background: #fff;">
            <summary style="cursor: pointer; font-weight: 600; outline: none;">Advanced Settings</summary>
            <table class="form-table" style="margin-top: 10px;">
                <tr>
                    <th scope="row">Enable Debug</th>
                    <td>
                        <input type="checkbox" name="li2_enable_debug" value="1" <?php checked(1, get_option('li2_enable_debug', 0), true); ?> />
                        <p class="description">Enable console debugging for the analytics script.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Dev Mode</th>
                    <td>
                        <input type="checkbox" name="li2_dev_mode" value="1" <?php checked(1, get_option('li2_dev_mode', 0), true); ?> />
                        <p class="description">Send events to the development API instead of production.</p>
                    </td>
                </tr>
                <?php /* remote config
                <tr>
                    <th scope="row">Cookie Options (JSON)</th>
                    <td>
                        <input type="text" name="li2_cookie_options" ... />
                    </td>
                </tr>
                */ ?>
            </table>
        </details>

        <h2 class="title">Conversion Tracking</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Track Registrations</th>
                <td>
                    <input type="checkbox" name="li2_track_registration" value="1" <?php checked(1, get_option('li2_track_registration', 1), true); ?> />
                    <p class="description">Send a lead event whenever a new user registers on your site.</p>
                </td>
            </tr>
        </table>

        <h2 class="title">WooCommerce Integration</h2>
        <?php if (class_exists('WooCommerce')) : ?>
            <p>WooCommerce is active. Select the events you want to track automatically.</p>
            <table class="form-table">
                <tr>
                    <th scope="row">Track Add to Cart (Lead)</th>
                    <td>
                        <input type="checkbox" name="li2_wc_track_add_to_cart" value="1" <?php checked(1, get_option('li2_wc_track_add_to_cart', 1), true); ?> />
                        <p class="description">Send a lead event when client adds a product to the cart.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Track Checkout (Sale)</th>
                    <td>
                        <input type="checkbox" name="li2_wc_track_sale" value="1" <?php checked(1, get_option('li2_wc_track_sale', 1), true); ?> />
                        <p class="description">Send a sale event when an order is created.</p>
                    </td>
                </tr>
            </table>
        <?php else : ?>
            <p style="color: red;"><strong>WooCommerce is not installed or activated.</strong></p>
        <?php endif; ?>

        <?php submit_button(); ?>
    </form>
</div>
<?php
}
