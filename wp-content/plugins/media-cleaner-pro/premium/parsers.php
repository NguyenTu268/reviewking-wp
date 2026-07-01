<?php

class MeowPro_WPMC_Parsers {

	public function __construct() {

		// ACF
		if ( class_exists( 'ACF' ) )
			require_once( 'parsers/acf.php' );

		// ACF Widgets
		if ( function_exists( 'acfw_globals' ) )  // mm change
			require_once( 'parsers/acf_widgets.php' );

		// Divi (ElegantThemes)
		if ( function_exists( '_et_core_find_latest' ) )
			require_once( 'parsers/divi.php' );

		// Visual Composer (WPBakery)
		if ( class_exists( 'Vc_Manager' ) )
			require_once( 'parsers/wpbakery_vc.php' );

		// Fusion Builder (Avada)
		if ( defined( 'FUSION_BUILDER_VERSION' ) || defined( 'FUSION_CORE_VERSION' ) || has_action( 'avada_author_info' ) )
			require_once( 'parsers/fusion_builder.php' );

		// Elementor
		if ( defined( 'ELEMENTOR_VERSION' ) ){
			require_once( 'parsers/elementor.php' );
		}

		// Beaver Builders
		if ( class_exists( 'FLBuilderModel' ) )
			require_once( 'parsers/beaver_builder.php' );

		// Oxygen Builder
		$breakdance_mode_oxygen = defined( 'BREAKDANCE_MODE' ) && BREAKDANCE_MODE === 'oxygen';
		if ( class_exists( 'Oxygen_VSB_Dynamic_Shortcodes' ) || $breakdance_mode_oxygen )
			require_once( 'parsers/oxygen_builder.php' );

		// Brizy
		if ( class_exists( 'Brizy_Editor_Post' ) )
			require_once( 'parsers/brizy.php' );

		// ZipList Recipe
		if ( function_exists( 'amd_zlrecipe_convert_to_recipe' ) )
			require_once( 'parsers/ziplist_recipe.php' );

		// UberMenu
		if ( class_exists( 'UberMenu' ) )
		require_once( 'parsers/ubermenu.php' );

		// X Theme
		if ( class_exists( 'X_Bootstrap' ) )
			require_once( 'parsers/theme-x.php' );

		// Easy Real Estate
		if ( class_exists( 'Easy_Real_Estate' ) )
			require_once( 'parsers/easy_real_estate.php' );

		// Tasty Pins
		if ( defined( 'TASTY_PINS_PLUGIN_FILE' ) )
			require_once( 'parsers/tasty_pins.php' );

		// WCFM MarketPlace
		if ( class_exists( 'WCFMmp' ) )
			require_once( 'parsers/wcfm_marketplace.php' );

		// Revolution Slider
		if ( class_exists( 'RevSliderFront' ) )
			require_once( 'parsers/revslider.php' );

		// WP Residence
		if ( defined( 'WPESTATE_PLUGIN_URL' ) )
			require_once( 'parsers/wpresidence.php' );

		// Avia Framework
		if ( defined( 'AV_FRAMEWORK_VERSION' ) ) {
			require_once( 'parsers/avia_framework.php' );

			//LayerSlider is shipped with the Enfold theme, so we need to check for it here too
			require_once( 'parsers/layerslider.php' );
		}

		// Fat Portfolio
		if ( class_exists( 'FAT_Portfolio' ) ) {
			require_once( 'parsers/fat_portfolio.php' );
		}

		// Yikes Custom Product Tabs
		if ( class_exists( 'YIKES_Custom_Product_Tabs' ) ) {
			require_once( 'parsers/custom_product_tabs.php' );
		}

		// Directories (SabaiApps)
		if ( function_exists( 'drts' ) ) {
			require_once( 'parsers/directories.php' );
		}

		// Image Map Pro
		if ( class_exists( 'ImageMapPro' ) ) {
			require_once( 'parsers/image_map_pro.php' );
		}

		// YooTheme Builder
		if ( class_exists( 'YOOtheme\\Builder' ) ) {
			require_once( 'parsers/yootheme_builder.php' );
		}

		// GeoDirectory
		if ( class_exists( 'geodirectory' ) ) {
			require_once( 'parsers/geodirectory.php' );
		}

		// Modula Gallery
		if ( class_exists( 'Modula' ) ) {
			require_once( 'parsers/modula_gallery.php' );
		}

		// Justified Image Grid
		if ( class_exists( 'JustifiedImageGrid' ) ) {
			require_once( 'parsers/justified_image_grid.php' );
		}

		// Advanced Ads
		if ( class_exists( 'Advanced_Ads' ) ) {
			require_once( 'parsers/advanced_ads.php' );
		}

		// SmartSlider
		if ( defined( 'SMARTSLIDER3_LIBRARY_PATH' ) ) {
			require_once( 'parsers/smart_slider.php' );
    	}

		// WebDirectory
		if ( class_exists( 'w2dc_plugin' ) ) {
			require_once( 'parsers/webdirectory.php' );
    }

		// Elfsight Slider
		// https://elfsight.com/
		if ( class_exists( 'ElfsightSliderPlugin' ) ) {
      require_once( 'parsers/elfsight_slider.php' );
    }

		// Nimble Builder
		if ( class_exists( '\Nimble\CZR_Fmk_Base' ) ) {
      require_once( 'parsers/nimble_builder.php' );
    }

		// Simple 3D Carousel
		if ( class_exists( 'fwds3dcar' ) ) {
      require_once( 'parsers/simple_3d_carousel.php' );
    }

		// Jet Engine
		if ( class_exists( 'Jet_Engine' ) ) {
      require_once( 'parsers/jet_engine.php' );
    }

		// Social Warfare
		if ( class_exists( 'Social_Warfare' ) ) {
			require_once( 'parsers/social_warfare.php' );
    }

		// Social Warfare
		if ( class_exists( 'WP_Job_Manager' ) ) {
			require_once( 'parsers/job_manager.php' );
    }

		// WP Discuz
		if ( class_exists( 'WpdiscuzCore' ) ) {
			require_once( 'parsers/wp_discuz.php' );
    }

		// Cornerstone
		if ( class_exists( 'Cornerstone_Plugin' ) ) {
			require_once( 'parsers/cornerstone.php' );
		}

		// Download Monitor
		if ( class_exists( 'WP_DLM' ) ) {
			require_once( 'parsers/download_monitor.php' );
		}

		// CM Business Directory
		if ( class_exists( 'CMBusinessDirectory' ) ) {
			require_once( 'parsers/cm_business_directory.php' );
    }

		// Sunshine Photo Cart
		if ( class_exists( 'SunshineCart' ) ) {
			require_once( 'parsers/sunshine_photo_cart.php' );
    }

		// Woodmart Theme
		if ( defined( 'WOODMART_CORE_VERSION' ) ) {
			require_once( 'parsers/woodmart.php' );
		}

		// Photo Gallery by 10Web
		if ( class_exists( 'BWG' ) ) {
			require_once( 'parsers/photo_gallery.php' );
		}

		// Web Stories
		if ( class_exists( 'Google\Web_Stories\Plugin' ) ) {
			require_once( 'parsers/web_stories.php' );
		}

		// Metabox
		if ( function_exists( 'rwmb_get_object_fields' ) ) {
      require_once( 'parsers/metabox.php' );
		}

		// Ultimate Responsive Image Slider
		if ( defined( 'URIS_PLUGIN_URL' ) ) {
			require_once( 'parsers/ur_image_slider.php' );
		}

		// Presto Player
		if ( defined( 'PRESTO_PLAYER_PLUGIN_URL' ) ) {
			require_once( 'parsers/presto_player.php' );
		}

		// Global Gallery
		if ( defined( 'GG_VER' ) ) {
			require_once( 'parsers/global_gallery.php' );
		}

		// Lana Download Manager
		if ( defined( 'LANA_DOWNLOADS_MANAGER_VERSION' ) ) {
			require_once( 'parsers/lana_downloads_manager.php' );
		}

		// PowerPress
		if ( defined( 'POWERPRESS_VERSION' ) ) {
			require_once( 'parsers/powerpress.php' );
		}

		// Connections Business Directory
		if ( class_exists( 'Connections_Directory' ) ) {
			require_once( 'parsers/connections_business_directory.php' );
		}

		// Wonderplugin 3D Carousel
		if ( defined( 'WONDERPLUGIN_3DCAROUSEL_VERSION' ) ) {
			require_once( 'parsers/wonderplugin_3dcarousel.php' );
		}

		// Uncode
		if ( defined( 'UNCODE_CORE_FILE' ) ) {
			require_once( 'parsers/uncode_core.php' );
		}

		// Mailpoet
		if ( defined( 'MAILPOET_MINIMUM_REQUIRED_WP_VERSION' ) ) {
			require_once( 'parsers/mailpoet.php' );
		}

		// Academy LMS
		if ( defined( 'ACADEMY_VERSION' ) ) {
			require_once( 'parsers/academy_lms.php' );
		}

		// Breakdance Builder
		if ( defined( 'BREAKDANCE_PLUGIN_URL' ) ) {
			require_once( 'parsers/breakdance_builder.php' );
		}

		// Bricks Builder
		if ( defined( 'BRICKS_VERSION' ) ) {
			require_once( 'parsers/bricks.php' );
		}

		// W3 Total Cache
		if ( defined( 'W3TC_VERSION' ) ) {
			require_once( 'parsers/w3_total_cache.php' );
		}

		// Spectra
		if ( defined( 'UAGB_PLUGIN_SHORT_NAME' ) ) {
			require_once( 'parsers/spectra.php' );
		}

		// Foo Gallery
		if ( defined( 'FOOGALLERY_VERSION' ) ) {
			require_once( 'parsers/foo_gallery.php' );
		}

		// Tutor LMS
		if ( defined( 'TUTOR_VERSION' ) ) {
			require_once( 'parsers/tutor_lms.php' );
		}

		// Houzez
		if ( defined( 'HOUZEZ_THEME_VERSION' ) ) {
			require_once( 'parsers/houzez.php' );
		}

		// Kadence Blocks
		if ( defined( 'KADENCE_BLOCKS_VERSION' ) ) {
			require_once( 'parsers/kadence_blocks.php' );
		}

		// Fluent Forms
		if ( defined( 'FLUENTFORM_VERSION' ) ) {
			require_once( 'parsers/fluent_forms.php' );
		}

		//Toolset
		if ( defined( 'TYPES_VERSION' ) || defined( 'WPCF_VERSION' ) ) {
			require_once( 'parsers/toolset.php' );
		}

		//LayerSlider
		if ( defined( 'LS_PLUGIN_VERSION' ) ) {
			require_once( 'parsers/layerslider.php' );
		}

		//WP Download Manager
		if ( defined( 'WPDM_VERSION' ) ) {
			require_once( 'parsers/wp_download_manager.php' );
		}
	}

}
