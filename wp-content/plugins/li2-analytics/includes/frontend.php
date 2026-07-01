<?php
if (!defined('ABSPATH')) {
    exit;
}

/* =========================================================
   LOAD LI2 CLIENT SCRIPT
========================================================= */

add_action('wp_head', 'li2_load_script');

function li2_load_script() {

    $publishable_key = get_option('li2_publishable_key');

    if (!$publishable_key) {
        return;
    }
    $dev_mode = get_option('li2_dev_mode', 0);
    // $track_pageview = get_option('li2_track_pageview', 1);   // remote config
    // $track_clicks = get_option('li2_track_clicks', 1);       // remote config
    // $track_scroll = get_option('li2_track_scroll', 1);       // remote config
    // $track_forms = get_option('li2_track_forms', 1);         // remote config
    $enable_debug = get_option('li2_enable_debug', 0);
    // $cookie_options = get_option('li2_cookie_options', '');  // remote config
?>

<script>
(function (c, n) {

    c[n] = c[n] || function () {
        (c[n].q = c[n].q || []).push(arguments);
    };

    ["trackLead", "trackSale", "trackEvent", "identify"].forEach(function (t) {
        c[n][t] = function () {
            c[n](t, ...arguments);
        };
    });

    var s = document.createElement("script");

    s.defer = true;
    s.src = "<?php echo $dev_mode ? 'https://cdn.li2.ai/dev/' . esc_js($publishable_key) : 'https://cdn.li2.ai/' . esc_js($publishable_key); ?>";

    s.setAttribute("data-publishable-key", "<?php echo esc_js($publishable_key); ?>");
    
    <?php if (false) : /* remote config — api-url now encoded in CDN path */ ?>
    s.setAttribute("data-api-url", "https://sapi-dev.quik.vn");
    <?php endif; ?>

    <?php if (false) : /* remote config */ ?>
    s.setAttribute("data-pageview", "true");
    <?php endif; ?>

    <?php if (false) : /* remote config */ ?>
    s.setAttribute("data-clicks", "true");
    <?php endif; ?>

    <?php if (false) : /* remote config */ ?>
    s.setAttribute("data-scroll", "true");
    <?php endif; ?>

    <?php if (false) : /* remote config */ ?>
    s.setAttribute("data-forms", "true");
    <?php endif; ?>

    <?php if ($enable_debug) : ?>
    s.setAttribute("data-debug", "true");
    <?php endif; ?>

    <?php if (false) : /* remote config */ ?>
    s.setAttribute("data-cookie-options", '<?php echo esc_js($cookie_options); ?>');
    <?php endif; ?>

    document.head.appendChild(s);

    <?php if ( isset( $_GET['li2_debug'] ) && sanitize_text_field( wp_unslash( $_GET['li2_debug'] ) ) === 'true' ) : ?>
    if (window.opener) {
        window.addEventListener('load', function () {
            window.opener.postMessage({
                type:               'li2_analytics_config_report',
                publishable_key:    '<?php echo esc_js( $publishable_key ); ?>',
                server_api_key:     '<?php echo esc_js( li2_mask_key( get_option( 'li2_api_key', '' ) ) ); ?>',
                dev_mode:           <?php echo $dev_mode ? 'true' : 'false'; ?>,
                track_registration: <?php echo get_option( 'li2_track_registration', 1 ) ? 'true' : 'false'; ?>,
                track_add_to_cart:  <?php echo get_option( 'li2_wc_track_add_to_cart', 1 ) ? 'true' : 'false'; ?>,
                track_checkout:     <?php echo get_option( 'li2_wc_track_sale', 1 ) ? 'true' : 'false'; ?>
            }, '*');
        });
    }
    <?php endif; ?>

})(window, "li2Analytics");
</script>

<?php
}
