<?php
/**
 * Plugin Name: LI2 Analytics
 * Description: LI2 tracking integration for WooCommerce
 * Version: 1.0.1
 * Author: LI2
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: li2-analytics
 */

if (!defined('ABSPATH')) {
    exit;
}

define('LI2_ANALYTICS_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Load modules
require_once LI2_ANALYTICS_PLUGIN_DIR . 'includes/settings.php';
require_once LI2_ANALYTICS_PLUGIN_DIR . 'includes/frontend.php';
require_once LI2_ANALYTICS_PLUGIN_DIR . 'includes/woocommerce.php';