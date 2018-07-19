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
 * Set the cookie to begin the countdown.
 *
 * @param  string $cart_item_key  The key from the cart.
 *
 * @return void
 */
function set_expiration_cookie( $cart_item_key = '' ) {

	// Get our amount of time to expire.
	$expire = Utilities\get_expiration_time();

	// Now create a JSON encoded array so we can check stuff later.
	$setup  = array( 'expire' => $expire, 'cart' => $cart_item_key );

	// And set the cookie.
	setcookie( Core\COOKIE_NAME, maybe_serialize( $setup ), $expire, '/' );
}

/**
 * Delete the cookie if it's present.
 *
 * @return void
 */
function delete_expiration_cookie() {

	// If we don't have the cookie, then no one cares.
	if ( ! isset( $_COOKIE[ Core\COOKIE_NAME ] ) ) {
		return;
	}

	// Unset the existing cookie.
	unset( $_COOKIE[ Core\COOKIE_NAME ] );

	// Now set a cookie in the past so it expires.
	setcookie( Core\COOKIE_NAME, '', time() - 3600, '/' );
}

/**
 * Delete the cookie if it's present.
 *
 * @return void
 */
function check_expiration_cookie() {

	// If we don't have the cookie, then no one cares.
	if ( ! isset( $_COOKIE[ Core\COOKIE_NAME ] ) ) {
		return;
	}

	// Strip out the slashes so we can get a clean array.
	$cookie = stripslashes( $_COOKIE[ Core\COOKIE_NAME ] );

	// Now pull out my cookie data.
	$cdata  = maybe_unserialize( $cookie );

	// Bail without a set of data or an expire time.
	if ( ! $cdata || empty( $cdata['expire'] ) ) {
		return false;
	}

	echo '<p>Right Now: ' . date( 'm/d/Y g:i a', time() ) . '</p>';
	echo '<p>Expire: ' . date( 'm/d/Y g:i a', absint( $cdata['expire'] ) ) . '</p>';

	// Return the amount of time or false for expired.
	if ( absint( $cdata['expire'] ) >= time() ) {
		die( 'got time left' );
	} else {
		die( 'expir4d' );
	}

	//echo time() - absint( $cdata['expire'] ); die();
	//die();
}
