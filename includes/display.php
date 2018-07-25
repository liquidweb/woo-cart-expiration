<?php
/**
 * Display our timer setup.
 *
 * @package WooCartExpiration
 */

// Declare our namespace.
namespace LiquidWeb\WooCartExpiration\Display;

// Set our aliases.
use LiquidWeb\WooCartExpiration as Core;
use LiquidWeb\WooCartExpiration\Utilities as Utilities;

/**
 * Start our engines.
 */
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_expiration_assets' );
add_action( 'wp_head', __NAMESPACE__ . '\load_expiration_metatag' );

/**
 * Load our front-end side JS and CSS.
 *
 * @return void
 */
function load_expiration_assets() {

	// Check if we're enabled or not.
	$enable = Utilities\maybe_expiration_enabled();

	// Bail if we aren't enabled.
	if ( ! $enable ) {
		return;
	}

	// Set my handle.
	$handle = 'woo-cart-expiration-front';

	// Set a file suffix structure based on whether or not we want a minified version.
	$file   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'woo-cart-expiration-front' : 'woo-cart-expiration-front.min';

	// Set a version for whether or not we're debugging.
	$vers   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : Core\VERS;

	// Set my localized variables.
	$local  = array(
		'ajaxurl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'woo_cart_timer_action' ),
		'interval' => apply_filters( Core\HOOK_PREFIX . 'timer_interval', 10000 ), // Time in seconds.
	);

	// Load our CSS file.
	wp_enqueue_style( $handle, Core\ASSETS_URL . '/css/' . $file . '.css', false, $vers, 'all' );

	// And our JS.
	wp_enqueue_script( $handle, Core\ASSETS_URL . '/js/' . $file . '.js', array( 'jquery' ), $vers, true );
	wp_localize_script( $handle, 'wooCartExpiration', $local );

	// Include our action let others load things.
	do_action( Core\HOOK_PREFIX . 'after_front_asset_load' );
}

/**
 * Include some meta tags to help our expiration JS.
 *
 * @return string
 */
function load_expiration_metatag() {

	// Run our inital check.
	$expire = Utilities\get_current_cookie_expiration();

	// Bail without a cookie.
	if ( ! $expire ) {
		return;
	}

	// Load up our meta tag.
	echo '<meta name="woo-cart-expiration" content="' . absint( $expire ) . '" />';

	// And include our timer markuo
	add_action( 'wp_footer', __NAMESPACE__ . '\load_timer_markup', 999 );
}

/**
 * Output the placeholder timer.
 *
 * @return HTML
 */
function load_timer_markup() {
	echo timer_markup_display();
}

/**
 * Build and retun the actual timer markup.
 *
 * @return HTML
 */
function timer_markup_display() {

	// Set an empty.
	$timer  = '';

	// Wrap the whole thing in a div.
	$timer .= '<div id="woo-cart-timer-wrap">';

		// Add a second div.
		$timer .= '<div class="woo-cart-timer-radial woo-cart-radial-animate">';

			// And a third div.
			$timer .= '<div class="woo-cart-timer-radial-half"></div>';
			$timer .= '<div class="woo-cart-timer-radial-half"></div>';

			// Include the two values inside of a paragraph.
			$timer .= '<p id="woo-cart-expire-countdown">';
				$timer .= '<span class="expire-value expire-minutes">0</span>';
				$timer .= '<span class="expire-value expire-seconds">00</span>';
			$timer .= '</p>';

		// Close up our inner div.
		$timer .= '</div>';

	// Close up our div.
	$timer .= '</div>';

	// Run our setup through a filter.
	return apply_filters( Core\HOOK_PREFIX . 'timer_markup', trim( $timer ) );
}
