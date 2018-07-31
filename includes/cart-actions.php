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
add_action( 'woocommerce_add_to_cart', __NAMESPACE__ . '\cart_added_action', 10, 6 );
add_action( 'woocommerce_cart_item_removed', __NAMESPACE__ . '\cart_removed_action', 10, 2 );
add_action( 'woocommerce_checkout_order_processed', __NAMESPACE__ . '\order_received_action', 10, 3 );

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
function cart_added_action( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

	// Check if we're enabled or not.
	$enable = Utilities\maybe_expiration_enabled();

	// Bail if we aren't enabled.
	if ( ! $enable ) {
		return;
	}

	// Go and set the cookie.
	Cookies\set_cookie( $cart_item_key );
}

/**
 * Set our actual timer once we've added something to the cart.
 *
 * @param string  $cart_item_key  The unique key of the cart action.
 * @param object  $cart_object    The entire cart object, which is passed.
 *
 * @return void
 */
function cart_removed_action( $cart_item_key, $cart_object ) {

	// Check the remaining items in the cart.
	$cart_count = absint( WC()->cart->get_cart_contents_count() );

	// If we still have stuff, continue as you were.
	if ( ! empty( $cart_count ) ) {
		return;
	}

	// Nothing left, so clear the cookies.
	Cookies\clear_cookie();
}

/**
 * Set our actual timer once we've added something to the cart.
 *
 * @param  integer $order_id     The order ID generated.
 * @param  array   $posted_data  The post data from the order.
 * @param  object  $order        The newly created order.
 *
 * @return void
 */
function order_received_action( $order_id, $posted_data, $order ) {

	// We don't care what the gateway
	// was, or even if it succeeded.
	Cookies\clear_cookie();
}
