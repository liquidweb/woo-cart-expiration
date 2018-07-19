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

/**
 * Start our engines.
 */
add_action( 'woocommerce_add_to_cart', __NAMESPACE__ . '\set_timer_on_cart', 10, 6 );

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


}
