<?php
/**
 * Our cookie-related functions.
 *
 * @package WooCartExpiration
 */

// Declare our namespace.
namespace LiquidWeb\WooCartExpiration\Cookies;

// Set our aliases.
use LiquidWeb\WooCartExpiration as Core;
use LiquidWeb\WooCartExpiration\Utilities as Utilities;

/**
 * Set up the args when we store a cookie.
 *
 * @param  integer $timer_expire  When the timer should expire.
 * @param  string  $cart_key      The cart key (which may be null).
 *
 * @return string                 The base64 encoded array.
 */
function build_cookie_args( $timer_expire = 0, $cart_key = '' ) {

	// Smash together the two pieces with a delimiter.
	$setup  = absint( $timer_expire ) . '|' . sanitize_key( $cart_key );

	// Return the base64 string.
	return base64_encode( $setup );
}

/**
 * Set the cookie to begin the countdown.
 *
 * @param  string $cart_item_key  The key from the cart.
 *
 * @return void
 */
function set_cookie( $cart_item_key = '' ) {

	// Get our amount of time to expire.
	$stamps = Utilities\get_initial_expiration_times();

	// Now create a JSON encoded array so we can check stuff later.
	$setup  = build_cookie_args( $stamps['expire'], $cart_item_key );

	// And set the cookie.
	setcookie( Core\COOKIE_NAME, $setup, absint( $stamps['cookie'] ), '/' );
}

/**
 * Delete the cookie if it's present.
 *
 * @return void
 */
function clear_cookie() {

	// Unset the existing cookie if we have it.
	if ( isset( $_COOKIE[ Core\COOKIE_NAME ] ) ) {
		unset( $_COOKIE[ Core\COOKIE_NAME ] );
	}

	// Set my reset time (in the past).
	$reset  = current_time( 'timestamp', true ) - 86400;

	// Now create a JSON encoded array so we can check stuff later.
	$setup  = build_cookie_args( 0, '' );

	// Now set a cookie in the past so it expires.
	setcookie( Core\COOKIE_NAME, $setup, absint( $reset ), '/' );
}

/**
 * Reset an existing cookie.
 *
 * @param  string $cart_item_key  The key from the cart.
 *
 * @return void
 */
function reset_cookie( $cart_item_key = '' ) {

	// Assuming we have it, remove it.
	if ( isset( $_COOKIE[ Core\COOKIE_NAME ] ) ) {

		// Unset the existing cookie.
		unset( $_COOKIE[ Core\COOKIE_NAME ] );
	}

	// Now set my cookie.
	set_cookie( $cart_item_key );
}

/**
 * Check the status of the cookie if it's present.
 *
 * @param  boolean $data  Whether to return the cookie data.
 *
 * @return mixed
 */
function check_cookie( $data = false ) {

	// If we don't have the cookie, then no one cares.
	if ( ! isset( $_COOKIE[ Core\COOKIE_NAME ] ) ) {
		return;
	}

	// First decode our cookie value.
	$decode = base64_decode( $_COOKIE[ Core\COOKIE_NAME ] );

	// Bail if we have no resulting value or it isn't serialized.
	if ( empty( $decode ) ) {
		return;
	}

	// Convert back to an array.
	$pieces = explode( '|', $decode );

	// Make sure it worked and that each one exists before moving.
	if ( empty( $pieces ) || ! is_array( $pieces ) || empty( $pieces[0] ) || empty( $pieces[1] ) ) {
		return false;
	}

	// If we requested the data, send it.
	if ( $data ) {
		return array( 'expire' => absint( $pieces[0] ), 'cart' => sanitize_key( $pieces[1] ) );
	}

	// Set my current time.
	$current_time   = current_time( 'timestamp', true );

	// Return true / false for expired.
	return absint( $pieces[0] ) >= absint( $current_time ) ? true : false;
}
