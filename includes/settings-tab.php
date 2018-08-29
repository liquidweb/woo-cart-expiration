<?php
/**
 * The functionality tied to the settings tab.
 *
 * @package WooCartExpiration
 */

// Declare our namespace.
namespace LiquidWeb\WooCartExpiration\SettingsTab;

// Set our aliases.
use LiquidWeb\WooCartExpiration as Core;
use LiquidWeb\WooCartExpiration\Utilities as Utilities;

/**
 * Start our engines.
 */
add_action( 'admin_head', __NAMESPACE__ . '\add_setting_field_css', 999 );
add_action( 'woocommerce_settings_tabs_general', __NAMESPACE__ . '\add_setting_link_anchor' );
add_action( 'woocommerce_admin_field_expiretime', __NAMESPACE__ . '\add_expiretime_setting_field' );
add_action( 'woocommerce_get_settings_general', __NAMESPACE__ . '\add_expiration_setting_args', 10 );
add_filter( 'plugin_action_links', __NAMESPACE__ . '\add_plugin_settings_link', 10, 2 );

/**
 * Add a small bit of CSS.
 */
function add_setting_field_css() {

	// Check if we are on the settings page.
	$maybe_settings = Utilities\maybe_admin_settings_page();

	// Bail if we aren't on the settings page.
	if ( ! $maybe_settings ) {
		return;
	}

	// Set my empty.
	$style  = '';

	// Open my style tag.
	$style .= '<style type="text/css">';

		// Set the field CSS.
		$style .= 'td.forminp-expiretime input#' . Core\OPTIONS_PREFIX . 'mins {';
			$style .= 'width: 50px;';
		$style .= '}' . "\n";

		// Set the inline description CSS.
		$style .= 'td.forminp-expiretime span.cart-expire-duration-description {';
			$style .= 'display: inline-block;';
			$style .= 'vertical-align: middle;';
			$style .= 'padding: 6px;';
		$style .= '}' . "\n";

	// Close my style tag.
	$style .= '</style>';

	// And echo it out.
	echo $style; // WPCS: XSS ok.
}

/**
 * Set a small div so we can anchor to it.
 *
 * @return void
 */
function add_setting_link_anchor() {
	echo '<div style="height:0;" id="' . sanitize_html_class( Core\SETTINGS_ANCHOR ) . '">&nbsp;</div>';
}

/**
 * A custom field for the duration because Woo is dumb.
 *
 * @param array $field  The array of values for the field.
 */
function add_expiretime_setting_field( $field ) {

	// Set my stored value for the field.
	$setting_value  = get_option( Core\OPTIONS_PREFIX . 'mins', $field['default'] );

	// Set my minimum and maximum ranges with a filter.
	$range_minimum  = apply_filters( Core\HOOK_PREFIX . 'range_min', $field['range']['min'], $field );
	$range_maximum  = apply_filters( Core\HOOK_PREFIX . 'range_max', $field['range']['max'], $field );

	// Begin building out the field.
	$build  = '';

	// Open the table row.
	$build .= '<tr valign="top">';

		// Set up the title column portion.
		$build .= '<th scope="row" class="titledesc">';

			// Open the label markup.
			$build .= '<label for="' . esc_attr( $field['id'] ) . '">';

				// Output the actual text.
				$build .= esc_html( $field['title'] );

				// Output the tooltip.
				$build .= wc_help_tip( $field['desc'] );

			// Close the label markup.
			$build .= '</label>';

		// Close the title column portion.
		$build .= '</th>';

		// Set up the field column portion.
		$build .= '<td class="forminp forminp-expiretime">';

			// Output the input field.
			$build .= '<input name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . absint( $setting_value ) . '" class="' . esc_attr( $field['class'] ) . '-field" type="number" min="' . absint( $range_minimum ) . '" max="' . absint( $range_maximum ) . '" step="1" />';

			// Output the secondary description.
			$build .= '<span class="description ' . esc_attr( $field['class'] ) . '-description">' . wp_kses_post( $field['suffix'] ) . '</span>';

		// Close the field column portion.
		$build .= '</td>';

	// Close the row.
	$build .= '</tr>';

	// And echo it out.
	echo $build; // WPCS: XSS ok.
}

/**
 * Add our new settings to the existing general tab in WooCommerce.
 *
 * @param  array  $settings  The current array of settings.
 *
 * @return HTML
 */
function add_expiration_setting_args( $settings ) {

	// Set up our array of settings data.
	$setup  = array(

		// Set the title for our section.
		array(
			'title' => __( 'Cart Expiration', 'woo-cart-expiration' ),
			'type'  => 'title',
			'desc'  => __( 'Configure the amount of time for user cart expiration.', 'woo-cart-expiration' ),
			'id'    => 'cart_expiration_options',
		),

		// Set the checkbox to enable it.
		array(
			'title'    => __( 'Enable cart expiration', 'woo-cart-expiration' ),
			'desc'     => __( 'Set a timer for each customer.', 'woocommerce' ),
			'id'       => Core\OPTIONS_PREFIX . 'enabled',
			'default'  => 'no',
			'desc_tip' => false,
			'type'     => 'checkbox',
		),

		// Enter the cart expiration time.
		array(
			'title'    => __( 'Expiration Time', 'woo-cart-expiration' ),
			'desc'     => __( 'Make sure to set a reasonable amount of time to allow your visitors to continue shopping.', 'woo-cart-expiration' ),
			'id'       => Core\OPTIONS_PREFIX . 'mins',
			'default'  => '15',
			'type'     => 'expiretime',
			'class'    => 'cart-expire-duration',
			'suffix'   => __( 'This sets the amount of time (in minutes) a customer has to check out.', 'woo-cart-expiration' ),
			'range'    => array( 'min' => 1, 'max' => 30 ),
		),

		// Close the section.
		array(
			'type' => 'sectionend',
			'id'   => 'cart_expiration_options',
		),

		// Nothing remaining to add.
	);

	// Run our setup through a filter.
	$setup  = apply_filters( Core\HOOK_PREFIX . 'setting_args', $setup, $settings );

	// Return the merged args (or the original if we wiped them out).
	return ! empty( $setup ) ? wp_parse_args( $setup, $settings ) : $settings;
}

/**
 * Add our "settings" links to the plugins page.
 *
 * @param  array  $links  The existing array of links.
 * @param  string $file   The file we are actually loading from.
 *
 * @return array  $links  The updated array of links.
 */
function add_plugin_settings_link( $links, $file ) {

	// Bail without caps.
	if ( ! current_user_can( 'manage_options' ) ) {
		return $links;
	}

	// Set the static var.
	static $this_plugin;

	// Check the base if we aren't paired up.
	if ( ! $this_plugin ) {
		$this_plugin = Core\BASE;
	}

	// Check to make sure we are on the correct plugin.
	if ( $file !== $this_plugin ) {
		return $links;
	}

	// Fetch our settings link.
	$link   = Utilities\get_settings_tab_link();

	// Now create the link markup.
	$setup  = '<a href="' . esc_url( $link ) . ' ">' . esc_html__( 'Settings', 'woo-cart-expiration' ) . '</a>';

	// Add it to the array.
	array_unshift( $links, $setup );

	// Return the resulting array.
	return $links;
}
