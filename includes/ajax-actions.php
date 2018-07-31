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
add_action( 'wp_ajax_woo_cart_reset_cookie_checkout', __NAMESPACE__ . '\reset_cart_cookie' );
add_action( 'wp_ajax_nopriv_woo_cart_reset_cookie_checkout', __NAMESPACE__ . '\reset_cart_cookie' );
add_action( 'wp_ajax_woo_cart_check_remaining_count', __NAMESPACE__ . '\check_cart_count' );
add_action( 'wp_ajax_nopriv_woo_cart_check_remaining_count', __NAMESPACE__ . '\check_cart_count' );
add_action( 'wp_ajax_woo_cart_get_timer_markup', __NAMESPACE__ . '\timer_markup' );
add_action( 'wp_ajax_nopriv_woo_cart_get_timer_markup', __NAMESPACE__ . '\timer_markup' );
add_action( 'wp_ajax_woo_cart_expiration_timer', __NAMESPACE__ . '\cart_timer' );
add_action( 'wp_ajax_nopriv_woo_cart_expiration_timer', __NAMESPACE__ . '\cart_timer' );

/**
 * Reset our cart cookie.
 *
 * @return mixed
 */
function reset_cart_cookie() {

	// Check our various constants.
	if ( ! Utilities\check_ajax_constants() ) {
		return;
	}

	// Check for the specific action.
	if ( empty( $_POST['action'] ) || 'woo_cart_reset_cookie_checkout' !== sanitize_text_field( $_POST['action'] ) ) { // WPCS: CSRF ok.
		return;
	}

	// Check to see if our nonce was provided.
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'woo_cart_reset_action' ) ) {
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
function check_cart_count() {

	// Check our various constants.
	if ( ! Utilities\check_ajax_constants() ) {
		return;
	}

	// Check for the specific action.
	if ( empty( $_POST['action'] ) || 'woo_cart_check_remaining_count' !== sanitize_text_field( $_POST['action'] ) ) { // WPCS: CSRF ok.
		return;
	}

	// Check to see if our nonce was provided.
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'woo_cart_count_action' ) ) {
		send_ajax_error_response( 'invalid-nonce' );
	}

	// Fetch the cart count.
	$cart_count = absint( WC()->cart->get_cart_contents_count() );
	$cart_bool  = ! empty( $cart_count ) ? false : true;

	// If we have nothing left, clear the cookie.
	if ( ! $cart_count ) {
		Cookies\clear_cookie();
	}

	// Send a response with the boolean of items in the cart remaining.
	send_ajax_success_response( array( 'empty' => $cart_bool ) );
}

/**
 * Get the markup for our timer and return it.
 *
 * @return mixed
 */
function timer_markup() {

	// Check our various constants.
	if ( ! Utilities\check_ajax_constants() ) {
		return;
	}

	// Check for the specific action.
	if ( empty( $_POST['action'] ) || 'woo_cart_get_timer_markup' !== sanitize_text_field( $_POST['action'] ) ) { // WPCS: CSRF ok.
		return;
	}

	// Check to see if our nonce was provided.
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'woo_markup_timer_action' ) ) {
		send_ajax_error_response( 'invalid-nonce' );
	}

	// Fetch the markup.
	$markup = Markup\timer_markup_display();

	// Send a response with the time remaining.
	send_ajax_success_response( array( 'markup' => $markup ) );
}

/**
 * Load up the timer actions.
 *
 * @return mixed
 */
function cart_timer() {

	// Check our various constants.
	if ( ! Utilities\check_ajax_constants() ) {
		return;
	}

	// Check for the specific action.
	if ( empty( $_POST['action'] ) || 'woo_cart_expiration_timer' !== sanitize_text_field( $_POST['action'] ) ) { // WPCS: CSRF ok.
		return;
	}

	// Check to see if our nonce was provided.
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'woo_cart_timer_action' ) ) {
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
 * Build and process our Ajax error handler.
 *
 * @param  string $errcode  The error code in question.
 *
 * @return array
 */
function send_ajax_error_response( $errcode = '' ) {

	// Build our return.
	$return = array(
		'errcode' => $errcode,
	);

	// And handle my JSON return.
	wp_send_json_error( $return );
}

/**
 * Build and process our Ajax success handler.
 *
 * @param  array $args  Any args to include in the response.
 *
 * @return array
 */
function send_ajax_success_response( $args = array() ) {

	// Build our return.
	$setup  = array( 'errcode' => null );

	// Add the args if we got them.
	$return = ! empty( $args ) ? wp_parse_args( $args, $setup ) : $setup;

	// And handle my JSON return.
	wp_send_json_success( $return );
}
