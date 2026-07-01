<?php

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}
?>
<div class="qodef-admin-settings-page">
	<form class="qodef-settings-list" id="qi_addons_for_elementor_settings_framework_ajax_form" data-options-name="qi_addons_for_elementor_settings">
		<div class="qodef-admin-settings-header">
			<div class="qodef-admin-settings-header-inner">
				<div class="qodef-settings-header-left">
					<div class="qodef-settings-header-left-inner">
						<p class="qodef-large">
							<?php echo esc_html__( 'Settings related to the plugin\'s functionality, including feature customization, tool integration, and performance adjustments, to better align with specific needs or requirements', 'qi-addons-for-elementor-premium' ); ?>
						</p>
					</div>
				</div>
				<div class="qodef-settings-header-right">
					<div class="qodef-settings-header-right-inner">
						<?php qi_addons_for_elementor_framework_template_part( QI_ADDONS_FOR_ELEMENTOR_ADMIN_PATH . '/inc', 'admin-pages', 'sub-pages/settings/templates/parts/save' ); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="qodef-settings-section">
			<div class="qodef-settings-section-title-holder">
				<h3 class="qodef-settings-section-title"><?php echo esc_html__( 'General', 'qi-addons-for-elementor' ); ?></h3>
			</div>
			<div class="qodef-settings-grid">
				<div class="qodef-settings-grid-inner">
					<div class="qodef-settings-item">
						<div class="qodef-settings-item-left">
							<div class="qodef-settings-item-top">
								<h4 class="qodef-settings-title">
									<span class="qodef-settings-title-inner">
										<?php echo esc_html__( 'Swiper Library Upgrade', 'qi-addons-for-elementor' ); ?>
									</span>
								</h4>
							</div>
							<div class="qodef-settings-item-bottom">
								<?php echo esc_html__( 'Upgrade the Swiper library from v5.4.5 to v8.4.5 to implement new slider improvements and features on the website', 'qi-addons-for-elementor' ); ?>
							</div>
						</div>
						<div class="qodef-settings-item-right">
							<div class="qodef-checkbox-toggle qodef-field" data-option-name="qi_addons_for_elementor_swiper_new">
								<input type="checkbox" id="qi_addons_for_elementor_swiper_new" name="qi_addons_for_elementor_swiper_new" value="yes" <?php echo isset( $new_swiper ) && $new_swiper !== 'no' ? 'checked' : ''; ?> />
								<label for="qi_addons_for_elementor_swiper_new"><?php echo esc_html__( 'Swiper Library Upgrade', 'qi-addons-for-elementor' ); ?></label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
