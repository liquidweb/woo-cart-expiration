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

/**
 * Start our engines.
 */
add_action( 'woocommerce_get_settings_advanced', __NAMESPACE__ . '\add_expiration_setting_args', 10, 2 );

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
