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

add_action( 'woocommerce_settings_tabs_advanced', __NAMESPACE__ . '\add_setting_link_anchor' );
add_action( 'woocommerce_get_settings_advanced', __NAMESPACE__ . '\add_expiration_setting_args', 10, 2 );
add_filter( 'plugin_action_links', __NAMESPACE__ . '\add_plugin_settings_link', 10, 2 );

/**
 * Set a small div so we can anchor to it.
 *
 * @return HTML
 */
function add_setting_link_anchor() {
	echo '<div style="height:0;" id="' . sanitize_html_class( Core\SETTINGS_ANCHOR ) . '">&nbsp;</div>';
}

/**
 * Add our new settings to the existing advanced tab in WooCommerce.
 *
 * @param  array  $settings         The current array of settings.
 * @param  string $current_section  The section we are on (if broken down).
 *
 * @return HTML
 */
function add_expiration_setting_args( $settings, $current_section ) {

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
			'title'             => __( 'Expiration Time', 'woo-cart-expiration' ),
			'desc'              => __( 'This sets the amount of time (in minutes) a customer has to check out.', 'woo-cart-expiration' ),
			'id'                => Core\OPTIONS_PREFIX . 'mins',
			'css'               => 'width:50px;',
			'default'           => '15',
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => array(
				'min'  => 1,
				'max'  => 30,
				'step' => 1,
			),
		),

		// Close the section.
		array(
			'type' => 'sectionend',
			'id'   => 'cart_expiration_options',
		),

		// Nothing remaining to add.
	);

	// Run our setup through a filter.
	$setup  = apply_filters( Core\HOOK_PREFIX . 'setting_args', $setup, $settings, $current_section );

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
	if ( $file != $this_plugin ) {
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
