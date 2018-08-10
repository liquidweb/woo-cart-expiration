<?php
/**
 * Handle the Ajax related timing.
 *
 * @package WooCartExpiration
 */

// Declare our namespace.
namespace LiquidWeb\WooCartExpiration\AjaxActions;

// Set our aliases.
use LiquidWeb\WooCartExpiration as Core;
use LiquidWeb\WooCartExpiration\Markup as Markup;
use LiquidWeb\WooCartExpiration\Cookies as Cookies;
use LiquidWeb\WooCartExpiration\Utilities as Utilities;

/**
 * Start our engines.
 */
add_action( 'wp_ajax_woo_cart_reset_cookie_checkout', __NAMESPACE__ . '\reset_cookie_checkout' );
add_action( 'wp_ajax_nopriv_woo_cart_reset_cookie_checkout', __NAMESPACE__ . '\reset_cookie_checkout' );
add_action( 'wp_ajax_woo_cart_check_remaining_count', __NAMESPACE__ . '\check_remaining_count' );
add_action( 'wp_ajax_nopriv_woo_cart_check_remaining_count', __NAMESPACE__ . '\check_remaining_count' );
add_action( 'wp_ajax_woo_cart_get_timer_markup', __NAMESPACE__ . '\get_timer_markup' );
add_action( 'wp_ajax_nopriv_woo_cart_get_timer_markup', __NAMESPACE__ . '\get_timer_markup' );
add_action( 'wp_ajax_woo_cart_expiration_timer', __NAMESPACE__ . '\expiration_timer' );
add_action( 'wp_ajax_nopriv_woo_cart_expiration_timer', __NAMESPACE__ . '\expiration_timer' );
add_action( 'wp_ajax_woo_cart_clear_expired_cart', __NAMESPACE__ . '\clear_expired_cart' );
add_action( 'wp_ajax_nopriv_woo_cart_clear_expired_cart', __NAMESPACE__ . '\clear_expired_cart' );

/**
 * Reset our cart cookie.
 *
 * @return mixed
 */
function reset_cookie_checkout() {

	// Check our various constants.
	if ( ! Utilities\check_ajax_constants( 'woo_cart_reset_cookie_checkout' ) ) {
		return;
	}

	// Check to see if our nonce was provided.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'woo_cart_reset_action' ) ) { // WPCS: CSRF ok.
		send_ajax_error_response( 'invalid-nonce' );
	}

	// Pull out my cart contents.
	$cart_contents  = array_keys( WC()->cart->get_cart_contents() );

	// If we have no contents, return.
	if ( ! $cart_contents ) {
		send_ajax_error_response( 'no-cart-contents' );
	}

	// Pull out the key value.
	$cart_item_key  = esc_attr( $cart_contents[0] );

	// Go and reset the cookie.
	Cookies\reset_cookie( $cart_item_key );

	// Send a response with the boolean of items in the cart remaining.
	send_ajax_success_response( array( 'cartkey' => $cart_item_key ) );
}

/**
 * Get the markup for our timer and return it.
 *
 * @return mixed
 */
function check_remaining_count() {

	// Check our various constants.
	if ( ! Utilities\check_ajax_constants( 'woo_cart_check_remaining_count' ) ) {
		return;
	}

	// Check to see if our nonce was provided.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'woo_cart_count_action' ) ) { // WPCS: CSRF ok.
		send_ajax_error_response( 'invalid-nonce' );
	}

	// Fetch the cart count.
	$cart_count = absint( WC()->cart->get_cart_contents_count() );
	$cart_bool  = ! empty( $cart_count ) ? false : true;

	// If we have nothing left, clear the cookie.
	if ( ! $cart_count ) {
		Utilities\clear_current_cart();
	}

	// Send a response with the boolean of items in the cart remaining.
	send_ajax_success_response( array( 'empty' => $cart_bool ) );
}

/**
 * Get the markup for our timer and return it.
 *
 * @return mixed
 */
function get_timer_markup() {

	// Check our various constants.
	if ( ! Utilities\check_ajax_constants( 'woo_cart_get_timer_markup' ) ) {
		return;
	}

	// Check to see if our nonce was provided.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'woo_markup_timer_action' ) ) { // WPCS: CSRF ok.
		send_ajax_error_response( 'invalid-nonce' );
	}

	// Fetch the individual markup items.
	$timer  = Markup\timer_markup_display();
	$modal  = Markup\expire_modal_display();

	// Set one variable to return.
	$markup = array( 'timer' => $timer, 'modal' => $modal );

	// Send a response with the markup.
	send_ajax_success_response( array( 'markup' => $markup ) );
}

/**
 * Load up the timer actions.
 *
 * @return mixed
 */
function expiration_timer() {

	// Check our various constants.
	if ( ! Utilities\check_ajax_constants( 'woo_cart_expiration_timer' ) ) {
		return;
	}

	// Check to see if our nonce was provided.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'woo_cart_timer_action' ) ) { // WPCS: CSRF ok.
		send_ajax_error_response( 'invalid-nonce' );
	}

	// Determine the amount of time remaining.
	$remain = Utilities\get_current_cookie_expiration( true );

	// If no remaining time is left, send the expired.
	if ( ! $remain || absint( $remain ) < 1 ) {

		// We hit expire, so clear it.
		Utilities\clear_current_cart();

		// And send the response.
		send_ajax_error_response( 'expired' );
	}

	// Send a response with the time remaining.
	send_ajax_success_response( array( 'remain' => absint( $remain ) ) );
}

/**
 * Clear out the cart as needed.
 *
 * @return mixed
 */
function clear_expired_cart() {

	// Check our various constants.
	if ( ! Utilities\check_ajax_constants( 'woo_cart_clear_expired_cart' ) ) {
		return;
	}

	// Check to see if our nonce was provided.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'woo_cart_killit_action' ) ) { // WPCS: CSRF ok.
		send_ajax_error_response( 'invalid-nonce' );
	}

	// This one doesn't really think. So call with care.
	Utilities\clear_current_cart();

	// Send a response with the time remaining.
	send_ajax_success_response( array( 'cleared' => true ) );
}

/**
 * Build and process our Ajax error handler.
 *
 * @param  string $errcode  The error code in question.
 * @param  array  $args     Any args to include in the response.
 *
 * @return json
 */
function send_ajax_error_response( $errcode = '', $args = array() ) {

	// Build our return.
	$setup  = array( 'errcode' => sanitize_text_field( $errcode ) );

	// Add the args if we got them.
	$return = ! empty( $args ) ? wp_parse_args( $args, $setup ) : $setup;

	// And handle my JSON return.
	wp_send_json_error( $return );
}

/**
 * Build and process our Ajax success handler.
 *
 * @param  array $args  Any args to include in the response.
 *
 * @return json
 */
function send_ajax_success_response( $args = array() ) {

	// Build our return.
	$setup  = array( 'errcode' => null );

	// Add the args if we got them.
	$return = ! empty( $args ) ? wp_parse_args( $args, $setup ) : $setup;

	// And handle my JSON return.
	wp_send_json_success( $return );
}
