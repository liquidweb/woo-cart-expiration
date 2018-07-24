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
use LiquidWeb\WooCartExpiration\Cookies as Cookies;
use LiquidWeb\WooCartExpiration\Utilities as Utilities;

/**
 * Start our engines.
 */
add_action( 'wp_ajax_woo_cart_expiration_timer', __NAMESPACE__ . '\cart_timer' );
add_action( 'wp_ajax_nopriv_woo_cart_expiration_timer', __NAMESPACE__ . '\cart_timer' );

/**
 * Update our user opt-in values.
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
	send_ajax_success_response( $remain );
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
 * @param  integer $remain  The amount of seconds remaining.
 *
 * @return array
 */
function send_ajax_success_response( $remain = 0 ) {

	// Build our return.
	$return = array(
		'errcode' => null,
		'remain'  => absint( $remain ),
	);

	// And handle my JSON return.
	wp_send_json_success( $return );
}
