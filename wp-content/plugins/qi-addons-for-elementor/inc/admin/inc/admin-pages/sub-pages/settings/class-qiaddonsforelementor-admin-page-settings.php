<?php

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

if ( ! function_exists( 'qi_addons_for_elementor_add_settings_sub_page_to_list' ) ) {
	/**
	 * Function that add additional sub page item into general page list
	 *
	 * @param array $sub_pages
	 *
	 * @return array
	 */
	function qi_addons_for_elementor_add_settings_sub_page_to_list( $sub_pages ) {
		$sub_pages[] = 'QiAddonsForElementor_Admin_Page_Settings';

		return $sub_pages;
	}

	add_filter( 'qi_addons_for_elementor_filter_add_welcome_sub_page', 'qi_addons_for_elementor_add_settings_sub_page_to_list' );
}

if ( class_exists( 'QiAddonsForElementor_Admin_Sub_Pages' ) ) {
	class QiAddonsForElementor_Admin_Page_Settings extends QiAddonsForElementor_Admin_Sub_Pages {

		public function __construct() {

			parent::__construct();

			add_filter( 'qi_addons_for_elementor_filter_hidden_submenu_pages', array( $this, 'add_to_hidden_submenu_pages' ) );

			add_action( 'wp_ajax_qi_addons_for_elementor_action_settings_save_options', array( $this, 'save_settings' ) );
		}

		public function add_sub_page() {
			$this->set_base( 'settings' );
			$this->set_menu_slug( 'qi_addons_for_elementor_settings' );
			$this->set_title( esc_html__( 'Settings', 'qi-addons-for-elementor' ) );
			$this->set_position( 4 );
			$this->set_atts( $this->set_atributtes() );
		}

		public function set_atributtes() {

			$new_swiper = get_option( 'qi_addons_for_elementor_swiper_new' ) == 'no' ? 'no' : 'yes';

			$atts = array(
				'new_swiper' => $new_swiper,
			);

			return $atts;
		}


		public function save_settings() {

			if ( current_user_can( 'edit_theme_options' ) ) {

				$_REQUEST = stripslashes_deep( $_REQUEST );
				unset( $_REQUEST['action'] );
				check_ajax_referer( 'qi_addons_for_elementor_settings_ajax_save_nonce', 'qi_addons_for_elementor_settings_ajax_save_nonce' );

				$new_swiper = $_REQUEST['qi_addons_for_elementor_swiper_new'] == 'yes' ? 'yes' : 'no';
				$results    = update_option( 'qi_addons_for_elementor_swiper_new', $new_swiper );

				do_action( 'qi_addons_for_elementor_action_saved_settings' );

				if ( $results ) {
					esc_html_e( 'Saved', 'qi-addons-for-elementor' );
				}

				die();
			}
		}

		public function add_to_hidden_submenu_pages( $hidden_submenu_pages ) {
			$hidden_submenu_pages[] = $this->get_menu_slug();

			return $hidden_submenu_pages;
		}
	}
}
