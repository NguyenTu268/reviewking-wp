<?php

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

if ( ! empty( $item_text ) ) {
	?>
	<p class="qodef-e-text">
		<?php echo qi_addons_for_elementor_framework_wp_kses_html( 'content', $item_text ); ?>
	</p>
<?php } ?>
