<?php
/**
 * Various utilities we use.
 *
 * @package WooCartExpiration
 */

// Declare our namespace.
namespace LiquidWeb\WooCartExpiration\Utilities;

// Set our aliases.
use LiquidWeb\WooCartExpiration as Core;
use LiquidWeb\WooCartExpiration\Cookies as Cookies;

/**
 * Check to see if the expiration is indeed enabled.
 *
 * @return boolean
 */
function maybe_expiration_enabled() {

	// Pull the option.
	$enable = get_option( Core\OPTIONS_PREFIX . 'enabled', 'no' );

	// Return a simple true / false.
	return 'yes' === sanitize_text_field( $enable ) ? true : false;
}

/**
 * Calculate the expiration time for a new cookie.
 *
 * @return array  The cart expire and actual cookie (which is more).
 */
function get_initial_expiration_times() {

	// Fetch the amount of time.
	$stored = get_option( Core\OPTIONS_PREFIX . 'mins', 15 );

	// Set the car expire time.
	$expire = current_time( 'timestamp', true ) + ( absint( $stored ) * MINUTE_IN_SECONDS );

	// Set an actual expire, which is 7 additional minutes.
	$cookie = absint( $expire ) + 420;

	// Return the array.
	return array( 'expire' => $expire, 'cookie' => $cookie );
}

/**
 * Calc the amount of time left on a cookie.
 *
 * @return array  The cart expire and actual cookie (which is more).
 */
function get_current_cookie_expiration() {

	// First get the cookie data.
	$cookie = Cookies\check_cookie( true );

	// Bail without the data.
	if ( ! $cookie || empty( $cookie['expire'] ) ) {
		return 0;
	}

	// Set my current time.
	$current_time   = current_time( 'timestamp', true );

	// Return the remaining amount, or a zero.
	return absint( $cookie['expire'] ) >= absint( $current_time ) ? absint( $cookie['expire'] ) : 0;
}

/**
 * Call our function that empties the cart.
 *
 * @return void
 */
function clear_current_cart() {

	// Set our global for Woo.
	global $woocommerce;

	// Run the cart empty setup.
	$woocommerce->cart->empty_cart();

	// Now recalc the totals.
	$woocommerce->cart->calculate_totals();

	// And clear out the cookie.
	Cookies\clear_cookie();
}

/**
 * Check our various constants on an Ajax call.
 *
 * @return boolean
 */
function check_ajax_constants() {

	// Check for a REST API request.
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}

	// Check for running an autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}

	// Check for running a cron, unless we've skipped that.
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		return false;
	}

	// We hit none of the checks, so proceed.
	return true;
}
