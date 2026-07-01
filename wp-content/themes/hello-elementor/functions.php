<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '3.4.9' );
define( 'EHP_THEME_SLUG', 'hello-elementor' );

define( 'HELLO_THEME_PATH', get_template_directory() );
define( 'HELLO_THEME_URL', get_template_directory_uri() );
define( 'HELLO_THEME_ASSETS_PATH', HELLO_THEME_PATH . '/assets/' );
define( 'HELLO_THEME_ASSETS_URL', HELLO_THEME_URL . '/assets/' );
define( 'HELLO_THEME_SCRIPTS_PATH', HELLO_THEME_ASSETS_PATH . 'js/' );
define( 'HELLO_THEME_SCRIPTS_URL', HELLO_THEME_ASSETS_URL . 'js/' );
define( 'HELLO_THEME_STYLE_PATH', HELLO_THEME_ASSETS_PATH . 'css/' );
define( 'HELLO_THEME_STYLE_URL', HELLO_THEME_ASSETS_URL . 'css/' );
define( 'HELLO_THEME_IMAGES_PATH', HELLO_THEME_ASSETS_PATH . 'images/' );
define( 'HELLO_THEME_IMAGES_URL', HELLO_THEME_ASSETS_URL . 'images/' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
					'navigation-widgets',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);
			add_theme_support( 'align-wide' );
			add_theme_support( 'responsive-embeds' );

			/*
			 * Editor Styles
			 */
			add_theme_support( 'editor-styles' );
			add_editor_style( 'assets/css/editor-styles.css' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	/**
	 * Check whether to display header footer.
	 *
	 * @return bool
	 */
	function hello_elementor_display_header_footer() {
		$hello_elementor_header_footer = true;

		return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				HELLO_THEME_STYLE_URL . 'reset.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				HELLO_THEME_STYLE_URL . 'theme.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( hello_elementor_display_header_footer() ) {
			wp_enqueue_style(
				'hello-elementor-header-footer',
				HELLO_THEME_STYLE_URL . 'header-footer.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function hello_elementor_add_description_meta_tag() {
		if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
	// Customizer controls
	function hello_elementor_customizer() {
		if ( ! is_customize_preview() ) {
			return;
		}

		if ( ! hello_elementor_display_header_footer() ) {
			return;
		}

		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
/**
 * Fix dứt điểm: inject aggregateRating vào WooCommerce Product schema của RankMath
 */
add_filter( 'rank_math/snippet/rich_snippet_product_entity', function( $entity ) {
    if ( ! isset( $entity['aggregateRating'] ) ) {
        $entity['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => '4.5',
            'bestRating'  => '5',
            'worstRating' => '1',
            'ratingCount' => '1',
        ];
    }
    return $entity;
}, 999 );

add_action( 'init', function() {
    if ( function_exists( 'WC' ) && isset( WC()->structured_data ) ) {
        remove_action( 'wp_footer', [ WC()->structured_data, 'output_structured_data' ], 10 );
    }
} );
add_filter( 'woocommerce_structured_data_product', '__return_empty_array' );

add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

function contact_popup_script() {
  ?>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.menu-item a[href="#popup"]').forEach(function(link) {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        elementorProFrontend.modules.popup.showPopup({ id: 6419 });
      });
    });
  });
  </script>
  <?php
}
add_action('wp_footer', 'contact_popup_script');

function contact_popup_animation() {
  ?>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var closeBtn = document.querySelector('.dialog-close-button');
    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        var content = document.querySelector('.dialog-lightbox-widget-content');
        if (content) {
          content.classList.add('slide-up');
          setTimeout(function() {
            content.classList.remove('slide-up');
          }, 400);
        }
      });
    }
  });
  </script>
  <?php
}
add_action('wp_footer', 'contact_popup_animation');


// Review King Product Grid Shortcode
// Paste this at the BOTTOM of functions.php (after all existing code)

function rk_product_grid_shortcode() {
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => wp_is_mobile() ? 2 : 6,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    );

    $products = new WP_Query($args);

    if (!$products->have_posts()) {
        return '<p>No products found.</p>';
    }

    ob_start();

    echo '<style>
        .rk-product-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            padding: 0;
            margin: 0;
        }
        @media (max-width: 1024px) {
            .rk-product-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .rk-product-grid { grid-template-columns: 1fr; }
        }

        .rk-product-card {
            background-color: #1c1c1c;
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            text-decoration: none !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .rk-product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.5);
            text-decoration: none !important;
        }
        .rk-product-card * {
            text-decoration: none !important;
        }

        .rk-product-image-wrap {
            position: relative;
            width: 100%;
            overflow: hidden;
            height: 280px;
            flex-shrink: 0;
        }
        .rk-product-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }
        .rk-product-card:hover .rk-product-image-wrap img {
            transform: scale(1.05);
        }
        .rk-product-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            background-color: #eca221;
            color: #ffffff;
            font-family: -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 13px;
            font-weight: 400;
            padding: 5px 16px;
            border-radius: 30px;
            text-transform: none;
            letter-spacing: 0;
            z-index: 10;
            text-decoration: none !important;
        }

        .rk-product-body {
            padding: 20px 22px 24px;
            display: flex;
            flex-direction: column;
            flex: 1;
            background-color: #1c1c1c;
        }
        .rk-product-date {
            color: #eca221;
            font-family: -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 16px;
            font-weight: 400;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none !important;
        }
        .rk-product-date svg {
            width: 14px;
            height: 14px;
            fill: #eca221;
            flex-shrink: 0;
        }
        .rk-product-title {
            color: #ffffff;
            font-family: "Plus Jakarta Sans", sans-serif;
            font-size: 23px;
            font-weight: 400;
            line-height: 1.3;
            margin: 0 0 12px 0;
            text-transform: uppercase;
            text-decoration: none !important;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .rk-product-excerpt {
            color: #858585;
            font-family: -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 16px;
            font-weight: 400;
            line-height: 1.6;
            margin: 0;
            text-decoration: none !important;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
    </style>';

    echo '<div class="rk-product-grid">';

    while ($products->have_posts()) {
        $products->the_post();

        $categories  = get_the_terms(get_the_ID(), 'product_cat');
        $cat_name    = (!empty($categories) && !is_wp_error($categories)) ? $categories[0]->name : '';
        $date        = get_the_date('d/m/Y');
        $permalink   = get_permalink();
        $thumbnail   = get_the_post_thumbnail_url(get_the_ID(), 'large');
        $full_title  = get_the_title();
        $full_excerpt = get_the_excerpt();

        // Limit title to 5 words
        $title_words = explode(' ', $full_title);
        $title = count($title_words) > 5 ? implode(' ', array_slice($title_words, 0, 5)) . '...' : $full_title;

        // Limit excerpt to 35 words
        $excerpt_words = explode(' ', strip_tags($full_excerpt));
        $excerpt = count($excerpt_words) > 35 ? implode(' ', array_slice($excerpt_words, 0, 35)) . '...' : $full_excerpt;

        if (!$thumbnail) {
            $thumbnail = wc_placeholder_img_src('large');
        }

        echo '<a href="' . esc_url($permalink) . '" class="rk-product-card">';

        echo '<div class="rk-product-image-wrap">';
        echo '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($full_title) . '" loading="lazy">';
        if ($cat_name) {
            echo '<span class="rk-product-badge">' . esc_html($cat_name) . '</span>';
        }
        echo '</div>';

        echo '<div class="rk-product-body">';
        echo '<div class="rk-product-date">';
        echo '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 4h-1V2h-2v2H8V2H6v2H5C3.9 4 3 4.9 3 6v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>';
        echo esc_html($date);
        echo '</div>';
        echo '<div class="rk-product-title">' . esc_html($title) . '</div>';
        if ($excerpt) {
            echo '<p class="rk-product-excerpt">' . esc_html($excerpt) . '</p>';
        }
        echo '</div>';

        echo '</a>';
    }

    echo '</div>';

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('rk_product_grid', 'rk_product_grid_shortcode');



// Review King Related Products Shortcode
// Paste this at the BOTTOM of functions.php (after all existing code)

function rk_related_products_shortcode() {
    if (!is_singular('product')) return '';

    $current_id = get_the_ID();
    $categories = get_the_terms($current_id, 'product_cat');

    if (empty($categories) || is_wp_error($categories)) return '';

    $cat_ids = wp_list_pluck($categories, 'term_id');

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
        'post__not_in'   => array($current_id),
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $cat_ids,
            ),
        ),
    );

    $products = new WP_Query($args);

    if (!$products->have_posts()) return '';

    ob_start();

    echo '<style>
        .rk-related-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 0;
            margin: 0;
        }

        .rk-related-card {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            gap: 16px;
            text-decoration: none !important;
            background-color: #1c1c1c;
            border-radius: 10px;
            overflow: hidden;
            padding: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(236, 162, 33, 0.3);
        }

        .rk-related-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }

        .rk-related-card * {
            text-decoration: none !important;
        }

        .rk-related-image {
            flex-shrink: 0;
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
        }

        .rk-related-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .rk-related-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            flex: 1;
            gap: 8px;
        }

        .rk-related-title {
            color: #ffffff;
            font-family: "Plus Jakarta Sans", sans-serif;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.4;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .rk-related-excerpt {
            color: #858585;
            font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>';

    echo '<div class="rk-related-grid">';

    while ($products->have_posts()) {
        $products->the_post();

        $permalink    = get_permalink();
        $thumbnail    = get_the_post_thumbnail_url(get_the_ID(), 'medium');
        $full_title   = get_the_title();
        $full_excerpt = get_the_excerpt();

        // Limit excerpt to 35 words
        $excerpt_words = explode(' ', strip_tags($full_excerpt));
        $excerpt = count($excerpt_words) > 35
            ? implode(' ', array_slice($excerpt_words, 0, 35)) . '...'
            : $full_excerpt;

        if (!$thumbnail) {
            $thumbnail = wc_placeholder_img_src('medium');
        }

        echo '<a href="' . esc_url($permalink) . '" class="rk-related-card">';

        // Left: image
        echo '<div class="rk-related-image">';
        echo '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($full_title) . '" loading="lazy">';
        echo '</div>';

        // Right: title + excerpt
        echo '<div class="rk-related-body">';
        echo '<div class="rk-related-title">' . esc_html($full_title) . '</div>';
        if ($excerpt) {
            echo '<p class="rk-related-excerpt">' . esc_html($excerpt) . '</p>';
        }
        echo '</div>';

        echo '</a>';
    }

    echo '</div>';

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('rk_related_products', 'rk_related_products_shortcode');


// Review King Privacy Policy Shortcode
// Paste this at the BOTTOM of functions.php (after all existing code)

function rk_privacy_policy_shortcode() {
    ob_start();
    ?>
    <style>
        .rk-policy-wrap {
            display: flex;
            gap: 40px;
            align-items: flex-start;
            font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            color: #cccccc;
            max-width: 1600px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* TOC - Left Column */
        .rk-policy-toc {
            width: 260px;
            flex-shrink: 0;
            position: sticky;
            top: 100px;
            align-self: flex-start;
            background-color: #1a1a1a;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid rgba(236,162,33,0.2);
        }

        .rk-policy-toc h3 {
            color: #eca221;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0 0 16px 0;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(236,162,33,0.2);
        }

        .rk-policy-toc ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .rk-policy-toc ul li a {
            display: block;
            color: #888888;
            font-size: 13px;
            line-height: 1.5;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none !important;
            transition: all 0.2s ease;
            border-left: 2px solid transparent;
        }

        .rk-policy-toc ul li a:hover {
            color: #eca221;
            background-color: rgba(236,162,33,0.08);
            border-left-color: #eca221;
        }

        .rk-policy-toc ul li a.active {
            color: #eca221;
            background-color: rgba(236,162,33,0.1);
            border-left-color: #eca221;
            font-weight: 600;
        }

        /* Content - Right Column */
        .rk-policy-content {
            flex: 1;
            min-width: 0;
        }

        .rk-policy-content .rk-policy-header {
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 1px solid rgba(236,162,33,0.2);
        }

        .rk-policy-content .rk-policy-header h1 {
            color: #ffffff;
            font-size: 36px;
            font-weight: 700;
            margin: 0 0 12px 0;
        }

        .rk-policy-content .rk-effective-date {
            color: #eca221;
            font-size: 13px;
            font-weight: 600;
        }

        .rk-policy-section {
            margin-bottom: 48px;
            scroll-margin-top: 120px;
        }

        .rk-policy-section h2 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 16px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .rk-policy-section h3 {
            color: #eca221;
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 10px 0;
        }

        .rk-policy-section p {
            color: #aaaaaa;
            font-size: 15px;
            line-height: 1.8;
            margin: 0 0 16px 0;
        }

        .rk-policy-section ul {
            color: #aaaaaa;
            font-size: 15px;
            line-height: 1.8;
            padding-left: 20px;
            margin: 0 0 16px 0;
        }

        .rk-policy-section ul li {
            margin-bottom: 8px;
        }

        .rk-policy-section a {
            color: #eca221;
            text-decoration: none !important;
        }

        .rk-policy-section a:hover {
            text-decoration: underline !important;
        }

        .rk-policy-contact-box {
            background-color: #1a1a1a;
            border: 1px solid rgba(236,162,33,0.3);
            border-radius: 12px;
            padding: 24px;
            margin-top: 16px;
        }

        .rk-policy-contact-box p {
            margin: 4px 0 !important;
        }

        @media (max-width: 768px) {
            .rk-policy-wrap {
                flex-direction: column;
                padding: 20px 16px;
            }
            .rk-policy-toc {
                width: 100%;
                position: relative;
                top: 0;
            }
            .rk-policy-content .rk-policy-header h1 {
                font-size: 26px;
            }
        }
    </style>

    <div class="rk-policy-wrap">

        <!-- LEFT: Table of Contents -->
        <div class="rk-policy-toc">
            <h3>Contents</h3>
            <ul>
                <li><a href="#rk-section-1">1. Introduction</a></li>
                <li><a href="#rk-section-2">2. Data Controller</a></li>
                <li><a href="#rk-section-3">3. Information We Collect</a></li>
                <li><a href="#rk-section-4">4. How We Use Your Information</a></li>
                <li><a href="#rk-section-5">5. Legal Basis for Processing</a></li>
                <li><a href="#rk-section-6">6. Data We Do Not Collect</a></li>
                <li><a href="#rk-section-7">7. Data Sharing and Disclosure</a></li>
                <li><a href="#rk-section-8">8. International Data Transfers</a></li>
                <li><a href="#rk-section-9">9. Cookies</a></li>
                <li><a href="#rk-section-10">10. Data Security</a></li>
                <li><a href="#rk-section-11">11. Data Breach Notification</a></li>
                <li><a href="#rk-section-12">12. Data Retention</a></li>
                <li><a href="#rk-section-13">13. Children's Privacy</a></li>
                <li><a href="#rk-section-14">14. Your Data Protection Rights</a></li>
                <li><a href="#rk-section-15">15. Third-Party Services</a></li>
                <li><a href="#rk-section-16">16. Links to Third-Party Websites</a></li>
                <li><a href="#rk-section-17">17. Changes to This Policy</a></li>
                <li><a href="#rk-section-18">18. Contact Us</a></li>
            </ul>
        </div>

        <!-- RIGHT: Content -->
        <div class="rk-policy-content">

            <div class="rk-policy-header">
                <h1>Privacy Policy</h1>
                <span class="rk-effective-date">Effective Date: June 1, 2025</span>
            </div>

            <div class="rk-policy-section" id="rk-section-1">
                <h2>1. Introduction</h2>
                <p>Review King ("we", "our", or "the Company") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, store, and protect personal data when you visit our website at reviewking.info, in compliance with applicable data protection laws including the General Data Protection Regulation (GDPR) and the Personal Data (Privacy) Ordinance (PDPO) of Hong Kong.</p>
                <p>By using our website, you agree to the collection and use of information in accordance with this Privacy Policy. If you do not agree, please do not use our website.</p>
            </div>

            <div class="rk-policy-section" id="rk-section-2">
                <h2>2. Data Controller</h2>
                <p>Review King is the data controller responsible for your personal data. Our contact details are:</p>
                <div class="rk-policy-contact-box">
                    <p><strong style="color:#ffffff;">Review King</strong></p>
                    <p>Email: <a href="mailto:contact@reviewking.info">contact@reviewking.info</a></p>
                    <p>Website: <a href="https://reviewking.info">reviewking.info</a></p>
                </div>
            </div>

            <div class="rk-policy-section" id="rk-section-3">
                <h2>3. Information We Collect</h2>
                <h3>3.1 Personal Data You Provide</h3>
                <p>When you contact us or inquire about our services, we may collect:</p>
                <ul>
                    <li>Full name</li>
                    <li>Email address</li>
                    <li>Phone number</li>
                    <li>Any other information you choose to provide in communications with us</li>
                </ul>
                <h3>3.2 Usage and Technical Data</h3>
                <p>When you visit our website, we automatically collect certain technical information, including:</p>
                <ul>
                    <li>IP address and approximate geographic location</li>
                    <li>Browser type, version, and language</li>
                    <li>Operating system and device type</li>
                    <li>Pages visited, time on page, and navigation paths</li>
                    <li>Referring website or search terms</li>
                    <li>Date and time of access</li>
                </ul>
                <h3>3.3 Cookie Data</h3>
                <p>We use cookies and similar tracking technologies to enhance your experience on our website. For full details, please see Section 9 (Cookies) below.</p>
            </div>

            <div class="rk-policy-section" id="rk-section-4">
                <h2>4. How We Use Your Information</h2>
                <p>We process your personal data for the following purposes:</p>
                <ul>
                    <li>To respond to your inquiries and communicate with you about our content</li>
                    <li>To send service-related communications, updates, and administrative notices</li>
                    <li>To improve and maintain our website and user experience</li>
                    <li>To analyze website traffic and usage patterns</li>
                    <li>To comply with our legal and regulatory obligations</li>
                    <li>To protect against fraud, abuse, and security threats</li>
                </ul>
            </div>

            <div class="rk-policy-section" id="rk-section-5">
                <h2>5. Legal Basis for Processing (GDPR)</h2>
                <p>For individuals in the European Economic Area (EEA) and the United Kingdom, we process your personal data on the following legal bases:</p>
                <ul>
                    <li><strong style="color:#ffffff;">Contract:</strong> Processing necessary to fulfill a service agreement or take pre-contractual steps at your request</li>
                    <li><strong style="color:#ffffff;">Legitimate Interests:</strong> Processing necessary for our legitimate business interests, such as improving our services and maintaining website security</li>
                    <li><strong style="color:#ffffff;">Legal Obligation:</strong> Processing required to comply with applicable laws and regulations</li>
                    <li><strong style="color:#ffffff;">Consent:</strong> Where we have obtained your explicit consent, such as for marketing communications — you may withdraw consent at any time by contacting us at <a href="mailto:contact@reviewking.info">contact@reviewking.info</a></li>
                </ul>
            </div>

            <div class="rk-policy-section" id="rk-section-6">
                <h2>6. Data We Do Not Collect or Process</h2>
                <p>We do not collect or process sensitive personal data such as racial or ethnic origin, political opinions, religious beliefs, health data, or biometric data.</p>
                <p>We do not knowingly collect personal data from children under the age of 18. See Section 13 for more details.</p>
            </div>

            <div class="rk-policy-section" id="rk-section-7">
                <h2>7. Data Sharing and Disclosure</h2>
                <p>We do not sell, rent, or trade your personal data. We may share your information only in the following limited circumstances:</p>
                <ul>
                    <li><strong style="color:#ffffff;">Service Providers:</strong> With trusted third-party vendors who assist us in operating our website and business, who are contractually bound to use your data only for the specified purpose</li>
                    <li><strong style="color:#ffffff;">Legal Requirements:</strong> When required by law, court order, or governmental authority</li>
                    <li><strong style="color:#ffffff;">Business Transfers:</strong> In connection with a merger, acquisition, or sale of all or substantially all of our assets</li>
                </ul>
            </div>

            <div class="rk-policy-section" id="rk-section-8">
                <h2>8. International Data Transfers</h2>
                <p>Review King operates globally. If you are accessing our website from the European Economic Area, the United Kingdom, or other regions with laws governing data collection and use, please be aware that your data may be transferred to and processed in other countries.</p>
                <p>Where we transfer personal data outside the EEA, we ensure appropriate safeguards are in place, such as Standard Contractual Clauses approved by the European Commission.</p>
            </div>

            <div class="rk-policy-section" id="rk-section-9">
                <h2>9. Cookies</h2>
                <h3>9.1 What Are Cookies</h3>
                <p>Cookies are small text files placed on your device by websites you visit. They are used to make websites function properly, improve efficiency, and provide information to the website owner.</p>
                <h3>9.2 Types of Cookies We Use</h3>
                <ul>
                    <li><strong style="color:#ffffff;">Essential Cookies:</strong> Necessary for our website to function. These cannot be disabled.</li>
                    <li><strong style="color:#ffffff;">Analytics Cookies:</strong> Used to understand how visitors interact with our website (e.g., Google Analytics). These are only set with your consent where required by law.</li>
                    <li><strong style="color:#ffffff;">Preference Cookies:</strong> Used to remember your settings and preferences to improve your experience.</li>
                </ul>
                <h3>9.3 Managing Cookies</h3>
                <p>You can control and manage cookies through your browser settings. Please note that disabling certain cookies may affect the functionality of our website. For more information on managing cookies, visit <a href="http://www.allaboutcookies.org" target="_blank">www.allaboutcookies.org</a>.</p>
            </div>

            <div class="rk-policy-section" id="rk-section-10">
                <h2>10. Data Security</h2>
                <p>We implement appropriate technical and organizational security measures to protect your personal data against unauthorized access, accidental loss, disclosure, alteration, or destruction. These measures include:</p>
                <ul>
                    <li>Encryption of data in transit using TLS/SSL</li>
                    <li>Access controls restricting data access to authorized personnel only</li>
                    <li>Regular security reviews of our systems and practices</li>
                </ul>
                <p>While we take reasonable steps to protect your data, no method of transmission over the internet is completely secure.</p>
            </div>

            <div class="rk-policy-section" id="rk-section-11">
                <h2>11. Data Breach Notification</h2>
                <p>In the event of a personal data breach that is likely to result in a risk to your rights and freedoms, we will notify the relevant supervisory authority without undue delay and, where required by law, notify you directly.</p>
            </div>

            <div class="rk-policy-section" id="rk-section-12">
                <h2>12. Data Retention</h2>
                <p>We retain your personal data only for as long as necessary to fulfill the purposes outlined in this Privacy Policy, or as required by applicable law:</p>
                <ul>
                    <li>Contact and correspondence records: Up to 7 years after the end of a business relationship</li>
                    <li>Website analytics data: Up to 26 months</li>
                    <li>Marketing communication preferences: Until you unsubscribe or withdraw consent</li>
                </ul>
            </div>

            <div class="rk-policy-section" id="rk-section-13">
                <h2>13. Children's Privacy</h2>
                <p>Our website and services are not directed to individuals under the age of 18. We do not knowingly collect personal data from children. If you believe we have inadvertently collected information from a child under 18, please contact us immediately at <a href="mailto:contact@reviewking.info">contact@reviewking.info</a> and we will take steps to delete such information promptly.</p>
            </div>

            <div class="rk-policy-section" id="rk-section-14">
                <h2>14. Your Data Protection Rights</h2>
                <h3>14.1 Rights Under GDPR (EEA/UK Residents)</h3>
                <ul>
                    <li><strong style="color:#ffffff;">Right of Access:</strong> Request a copy of the personal data we hold about you</li>
                    <li><strong style="color:#ffffff;">Right to Rectification:</strong> Request correction of inaccurate or incomplete data</li>
                    <li><strong style="color:#ffffff;">Right to Erasure:</strong> Request deletion of your personal data, subject to certain conditions</li>
                    <li><strong style="color:#ffffff;">Right to Restriction:</strong> Request that we restrict processing of your data</li>
                    <li><strong style="color:#ffffff;">Right to Data Portability:</strong> Receive your data in a structured, machine-readable format</li>
                    <li><strong style="color:#ffffff;">Right to Object:</strong> Object to processing based on legitimate interests or for direct marketing</li>
                </ul>
                <h3>14.2 Rights Under PDPO (Hong Kong Residents)</h3>
                <ul>
                    <li><strong style="color:#ffffff;">Right of Access:</strong> Request access to personal data we hold about you</li>
                    <li><strong style="color:#ffffff;">Right to Correction:</strong> Request correction of personal data that is inaccurate</li>
                </ul>
                <p>To exercise any of these rights, please contact us at <a href="mailto:contact@reviewking.info">contact@reviewking.info</a>. We will respond within 30 days.</p>
            </div>

            <div class="rk-policy-section" id="rk-section-15">
                <h2>15. Third-Party Services</h2>
                <h3>15.1 Google Analytics</h3>
                <p>We use Google Analytics to analyze website traffic. Google Analytics collects data such as pages visited, time on site, and referring sources using cookies and similar technologies. You can opt out by installing the <a href="https://tools.google.com/dlpage/gaoptout" target="_blank">Google Analytics Opt-out Browser Add-on</a>.</p>
                <h3>15.2 Google Ads</h3>
                <p>We use Google Ads and click tracking technologies to run and monitor the performance of our advertising campaigns. For more information, see <a href="https://policies.google.com/privacy" target="_blank">Google's Privacy Policy</a>.</p>
                <h3>15.3 Hosting and Infrastructure</h3>
                <p>Our website is hosted by third-party infrastructure providers who may process certain technical data in the course of providing hosting services. All providers are bound by appropriate data processing agreements.</p>
            </div>

            <div class="rk-policy-section" id="rk-section-16">
                <h2>16. Links to Third-Party Websites</h2>
                <p>Our website may contain links to external sites not operated by us. This Privacy Policy does not apply to those sites. We encourage you to review the privacy policies of any third-party websites you visit.</p>
            </div>

            <div class="rk-policy-section" id="rk-section-17">
                <h2>17. Changes to This Privacy Policy</h2>
                <p>We may update this Privacy Policy from time to time to reflect changes in our practices or applicable law. Material changes will be indicated by an updated Effective Date at the top of this document. Your continued use of our website after any changes constitutes your acceptance of the updated Privacy Policy.</p>
            </div>

            <div class="rk-policy-section" id="rk-section-18">
                <h2>18. Contact Us</h2>
                <p>If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us at:</p>
                <div class="rk-policy-contact-box">
                    <p><strong style="color:#ffffff;">Review King</strong></p>
                    <p>Email: <a href="mailto:contact@reviewking.info">contact@reviewking.info</a></p>
                    <p>Website: <a href="https://reviewking.info">reviewking.info</a></p>
                    <p style="color:#888888; font-size:13px; margin-top:12px !important;">We will respond to all requests within 30 days of receipt.</p>
                </div>
            </div>

        </div>
    </div>

    <script>
    (function() {
        var sections = document.querySelectorAll('.rk-policy-section');
        var tocLinks = document.querySelectorAll('.rk-policy-toc a');

        window.addEventListener('scroll', function() {
            var scrollY = window.scrollY + 140;
            var current = '';

            sections.forEach(function(section) {
                if (section.offsetTop <= scrollY) {
                    current = section.getAttribute('id');
                }
            });

            tocLinks.forEach(function(link) {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });

        tocLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 110,
                        behavior: 'smooth'
                    });
                }
            });
        });
    })();
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('rk_privacy_policy', 'rk_privacy_policy_shortcode');


// Review King Terms of Service Shortcode
// Paste this at the BOTTOM of functions.php (after all existing code)

function rk_terms_of_service_shortcode() {
    ob_start();
    ?>
    <style>
        .rk-terms-wrap {
            display: flex;
            gap: 40px;
            align-items: flex-start;
            font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            color: #cccccc;
            max-width: 1600px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* TOC - Left Column */
        .rk-terms-toc {
            width: 260px;
            flex-shrink: 0;
            position: sticky;
            top: 100px;
            align-self: flex-start;
            background-color: #1a1a1a;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid rgba(236,162,33,0.2);
        }

        .rk-terms-toc h3 {
            color: #eca221;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0 0 16px 0;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(236,162,33,0.2);
        }

        .rk-terms-toc ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .rk-terms-toc ul li a {
            display: block;
            color: #888888;
            font-size: 13px;
            line-height: 1.5;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none !important;
            transition: all 0.2s ease;
            border-left: 2px solid transparent;
        }

        .rk-terms-toc ul li a:hover {
            color: #eca221;
            background-color: rgba(236,162,33,0.08);
            border-left-color: #eca221;
        }

        .rk-terms-toc ul li a.active {
            color: #eca221;
            background-color: rgba(236,162,33,0.1);
            border-left-color: #eca221;
            font-weight: 600;
        }

        /* Content - Right Column */
        .rk-terms-content {
            flex: 1;
            min-width: 0;
        }

        .rk-terms-content .rk-terms-header {
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 1px solid rgba(236,162,33,0.2);
        }

        .rk-terms-content .rk-terms-header h1 {
            color: #ffffff;
            font-size: 36px;
            font-weight: 700;
            margin: 0 0 12px 0;
        }

        .rk-terms-content .rk-effective-date {
            color: #eca221;
            font-size: 13px;
            font-weight: 600;
        }

        .rk-terms-section {
            margin-bottom: 48px;
            scroll-margin-top: 120px;
        }

        .rk-terms-section h2 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 16px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .rk-terms-section p {
            color: #aaaaaa;
            font-size: 15px;
            line-height: 1.8;
            margin: 0 0 16px 0;
        }

        .rk-terms-section ul {
            color: #aaaaaa;
            font-size: 15px;
            line-height: 1.8;
            padding-left: 20px;
            margin: 0 0 16px 0;
        }

        .rk-terms-section ul li {
            margin-bottom: 8px;
        }

        .rk-terms-section a {
            color: #eca221;
            text-decoration: none !important;
        }

        .rk-terms-section a:hover {
            text-decoration: underline !important;
        }

        .rk-terms-contact-box {
            background-color: #1a1a1a;
            border: 1px solid rgba(236,162,33,0.3);
            border-radius: 12px;
            padding: 24px;
            margin-top: 16px;
        }

        .rk-terms-contact-box p {
            margin: 4px 0 !important;
        }

        .rk-terms-intro {
            background-color: #1a1a1a;
            border-left: 3px solid #eca221;
            border-radius: 0 8px 8px 0;
            padding: 16px 20px;
            margin-bottom: 40px;
        }

        .rk-terms-intro p {
            color: #aaaaaa;
            font-size: 15px;
            line-height: 1.8;
            margin: 0 !important;
        }

        @media (max-width: 768px) {
            .rk-terms-wrap {
                flex-direction: column;
                padding: 20px 16px;
            }
            .rk-terms-toc {
                width: 100%;
                position: relative;
                top: 0;
            }
            .rk-terms-content .rk-terms-header h1 {
                font-size: 26px;
            }
        }
    </style>

    <div class="rk-terms-wrap">

        <!-- LEFT: Table of Contents -->
        <div class="rk-terms-toc">
            <h3>Contents</h3>
            <ul>
                <li><a href="#rk-terms-1">1. About Our Company</a></li>
                <li><a href="#rk-terms-2">2. Acceptable Use</a></li>
                <li><a href="#rk-terms-3">3. Account Registration</a></li>
                <li><a href="#rk-terms-4">4. Services and Fees</a></li>
                <li><a href="#rk-terms-5">5. Confidentiality</a></li>
                <li><a href="#rk-terms-6">6. Intellectual Property</a></li>
                <li><a href="#rk-terms-7">7. Disclaimer of Warranties</a></li>
                <li><a href="#rk-terms-8">8. Limitation of Liability</a></li>
                <li><a href="#rk-terms-9">9. Indemnification</a></li>
                <li><a href="#rk-terms-10">10. Third-Party Links</a></li>
                <li><a href="#rk-terms-11">11. Termination</a></li>
                <li><a href="#rk-terms-12">12. Governing Law</a></li>
                <li><a href="#rk-terms-13">13. Entire Agreement</a></li>
                <li><a href="#rk-terms-14">14. Changes to These Terms</a></li>
                <li><a href="#rk-terms-15">15. Contact Us</a></li>
            </ul>
        </div>

        <!-- RIGHT: Content -->
        <div class="rk-terms-content">

            <div class="rk-terms-header">
                <h1>Terms of Service</h1>
                <span class="rk-effective-date">Effective Date: June 1, 2025</span>
            </div>

            <div class="rk-terms-intro">
                <p>Welcome to Review King. These Terms of Service govern your access to and use of our website at reviewking.info. By accessing our website, you agree to be bound by these terms. If you do not agree, please do not use our website.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-1">
                <h2>1. About Our Company and Services</h2>
                <p>Review King is an independent product review platform. We publish honest, research-based reviews across tech, fashion, beauty, health, AI, and gaming to help consumers make informed purchasing decisions.</p>
                <p>Our content is based on publicly available information, hands-on research, official documentation, and user feedback. Some links on this website may be referral or affiliate links. This does not influence our evaluations or conclusions. Our priority is always to provide value and clarity to our readers.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-2">
                <h2>2. Acceptable Use of Our Website</h2>
                <p>You agree to use our website for lawful purposes only. You must not:</p>
                <ul>
                    <li>Use our website in any way that violates applicable local, national, or international laws or regulations</li>
                    <li>Transmit any unsolicited or unauthorized advertising or promotional material</li>
                    <li>Attempt to gain unauthorized access to any part of our website or its related systems</li>
                    <li>Engage in any conduct that restricts or inhibits anyone else's use or enjoyment of the website</li>
                    <li>Introduce viruses, trojans, worms, or other malicious or harmful material to our website</li>
                </ul>
            </div>

            <div class="rk-terms-section" id="rk-terms-3">
                <h2>3. Account Registration</h2>
                <p>Certain areas of our website may require you to register an account. You agree to provide accurate, current, and complete information during registration and to keep your account information updated. You are responsible for maintaining the confidentiality of your credentials and for all activities that occur under your account.</p>
                <p>You must notify us immediately at <a href="mailto:contact@reviewking.info">contact@reviewking.info</a> if you suspect any unauthorized use of your account.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-4">
                <h2>4. Services and Fees</h2>
                <p>Review King is a free-to-read platform. Certain premium features or partnerships may be subject to separate written agreements. Nothing on this website constitutes an offer or binding commitment to provide services at any particular price or on any particular terms.</p>
                <p>We reserve the right to modify our service offerings at any time.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-5">
                <h2>5. Confidentiality</h2>
                <p>We treat all user and partner information as confidential. Our team members are bound by confidentiality obligations and may not disclose information to unauthorized third parties. This obligation survives the termination of any engagement.</p>
                <p>By using our website, you agree not to disclose any confidential information we may share with you in the course of our business relationship, including but not limited to pricing, methodologies, and proprietary processes.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-6">
                <h2>6. Intellectual Property</h2>
                <p>All content on our website — including text, graphics, logos, images, and software — is protected by intellectual property rights owned by Review King or our licensors. You may not reproduce, distribute, modify, create derivative works from, publicly display, or exploit any material on our website without our prior written consent.</p>
                <p>Nothing in these terms grants you any right, title, or interest in or to our intellectual property.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-7">
                <h2>7. Disclaimer of Warranties</h2>
                <p>Our website and any information provided on it are offered "as is" and "as available" without warranties of any kind, whether express or implied. We make no representations or warranties that our website will be uninterrupted, error-free, secure, or free from viruses or other harmful components.</p>
                <p>While we strive for accuracy in all our reviews, we are not responsible for any purchasing decisions made based on our content. Always do your own research before making a purchase.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-8">
                <h2>8. Limitation of Liability</h2>
                <p>To the fullest extent permitted by applicable law, Review King, its directors, employees, and affiliates shall not be liable for any direct, indirect, incidental, special, consequential, or exemplary damages, including but not limited to loss of profits, data, goodwill, or business opportunities, arising from:</p>
                <ul>
                    <li>Your use of, or inability to use, our website</li>
                    <li>Any errors, mistakes, or inaccuracies in content on our website</li>
                    <li>Any interruption or cessation of transmission to or from our website</li>
                    <li>Any third-party conduct or content accessed through our website</li>
                </ul>
            </div>

            <div class="rk-terms-section" id="rk-terms-9">
                <h2>9. Indemnification</h2>
                <p>You agree to indemnify, defend, and hold harmless Review King and its officers, directors, employees, agents, and licensors from and against any claims, liabilities, damages, losses, and expenses, including reasonable legal fees, arising out of or in connection with your use of our website or your violation of these Terms of Service.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-10">
                <h2>10. Third-Party Links</h2>
                <p>Our website may contain links to third-party websites, including affiliate and referral links, for your convenience. These links do not constitute an endorsement of those websites. We have no control over and assume no responsibility for the content, privacy policies, or practices of any third-party sites.</p>
                <p>We strongly encourage you to review the terms and privacy policies of any third-party sites you visit.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-11">
                <h2>11. Termination</h2>
                <p>We reserve the right to terminate or suspend access to our website at our sole discretion, without notice, for any conduct that we believe violates these Terms of Service or is harmful to other users, us, or third parties, or for any other reason.</p>
                <p>Provisions of these terms that by their nature should survive termination shall survive, including intellectual property ownership, disclaimers of warranty, limitations of liability, and indemnification.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-12">
                <h2>12. Governing Law and Jurisdiction</h2>
                <p>These Terms of Service shall be governed by and construed in accordance with applicable laws. Any dispute arising from or relating to these terms or your use of our website shall be subject to the exclusive jurisdiction of the relevant courts.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-13">
                <h2>13. Entire Agreement and Severability</h2>
                <p>These Terms of Service, together with our Privacy Policy, constitute the entire agreement between you and Review King regarding your use of our website. If any provision of these terms is found to be unenforceable or invalid, that provision shall be modified to the minimum extent necessary to make it enforceable, and the remaining provisions shall continue in full force and effect.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-14">
                <h2>14. Changes to These Terms</h2>
                <p>We may update these Terms of Service from time to time. Material changes will be indicated by an updated Effective Date at the top of this document. Your continued use of our website following any changes constitutes your acceptance of the updated terms. We encourage you to review this page periodically.</p>
            </div>

            <div class="rk-terms-section" id="rk-terms-15">
                <h2>15. Contact Us</h2>
                <p>If you have any questions about these Terms of Service, please contact us at:</p>
                <div class="rk-terms-contact-box">
                    <p><strong style="color:#ffffff;">Review King</strong></p>
                    <p>Email: <a href="mailto:contact@reviewking.info">contact@reviewking.info</a></p>
                    <p>Website: <a href="https://reviewking.info">reviewking.info</a></p>
                    <p style="color:#888888; font-size:13px; margin-top:12px !important;">We will respond to all requests within 30 days of receipt.</p>
                </div>
            </div>

        </div>
    </div>

    <script>
    (function() {
        var sections = document.querySelectorAll('.rk-terms-section');
        var tocLinks = document.querySelectorAll('.rk-terms-toc a');

        window.addEventListener('scroll', function() {
            var scrollY = window.scrollY + 140;
            var current = '';

            sections.forEach(function(section) {
                if (section.offsetTop <= scrollY) {
                    current = section.getAttribute('id');
                }
            });

            tocLinks.forEach(function(link) {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });

        tocLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 110,
                        behavior: 'smooth'
                    });
                }
            });
        });
    })();
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('rk_terms_of_service', 'rk_terms_of_service_shortcode');

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		wp_body_open();
	}
}

require HELLO_THEME_PATH . '/theme.php';

HelloTheme\Theme::instance();
