<?php
/**
 * The functionality tied to the "add to cart".
 *
 * @package WooCartExpiration
 */

// Declare our namespace.
namespace LiquidWeb\WooCartExpiration\CartActions;

// Set our aliases.
use LiquidWeb\WooCartExpiration as Core;
use LiquidWeb\WooCartExpiration\Cookies as Cookies;
use LiquidWeb\WooCartExpiration\Utilities as Utilities;

/**
 * Start our engines.
 */
add_action( 'init', __NAMESPACE__ . '\check_cart_timer', 1 );
add_action( 'woocommerce_add_to_cart', __NAMESPACE__ . '\set_timer_on_cart', 10, 6 );

/**
 * Run our check against the existing timer.
 *
 * @return void
 */
function check_cart_timer() {

	Cookies\check_expiration_cookie();



	/*
	global $woocommerce;
	$woocommerce->cart->empty_cart();
	*/
}

/**
 * Set our actual timer once we've added something to the cart.
 *
 * @param string  $cart_item_key  The unique key of the cart action.
 * @param integer $product_id     The ID of the product to add to the cart.
 * @param integer $quantity       The quantity of the item to add.
 * @param integer $variation_id   ID of the variation being added to the cart.
 * @param array   $variation      The variation data array.
 * @param array   $cart_item_data Any extra cart item data we want to pass into the item.
 *
 * @return void
 */
function set_timer_on_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

	// Check if we're enabled or not.
	$enable = Utilities\maybe_expiration_enabled();

	// Bail if we aren't enabled.
	if ( ! $enable ) {
		return;
	}

	// Go and set the cookie.
	Cookies\set_expiration_cookie( $cart_item_key );
}
