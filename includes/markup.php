<?php
/**
 * Handle the actual markup layouts.
 *
 * @package WooCartExpiration
 */

// Declare our namespace.
namespace LiquidWeb\WooCartExpiration\Markup;

// Set our aliases.
use LiquidWeb\WooCartExpiration as Core;
use LiquidWeb\WooCartExpiration\Utilities as Utilities;

/**
 * Build out and return the timer display.
 *
 * @param  boolean $echo  Whether to echo it out or return.
 *
 * @return HTML
 */
function timer_markup_display( $echo = false ) {

	// Grab the checkout URL if we have one.
	$check_page_url = wc_get_checkout_url();

	// Get my expiration time.
	$remaining_time = Utilities\get_current_cookie_remaining( 'array' );

	// Set my two pieces of data.
	$remain_minutes = ! empty( $remaining_time['minutes'] ) ? absint( $remaining_time['minutes'] ) : '0';
	$remain_seconds = ! empty( $remaining_time['seconds'] ) ? absint( $remaining_time['seconds'] ) : '00';

	// Set an empty.
	$timer  = '';

	// Wrap the whole thing in a div.
	$timer .= '<div id="woo-cart-timer-wrap-id" class="woo-cart-timer-wrap">';

		// Add a second div.
		$timer .= '<div class="woo-cart-timer-radial woo-cart-radial-animate">';

			// And a third div.
			$timer .= '<div class="woo-cart-timer-radial-half"></div>';
			$timer .= '<div class="woo-cart-timer-radial-half"></div>';

			// Include the two values inside of a paragraph.
			$timer .= '<p id="woo-cart-expire-countdown">';
				$timer .= '<a title="' . esc_attr__( 'Click here to check out', 'woo-cart-expiration' ) . '" href="' . esc_url( $check_page_url ) . '">';
					$timer .= '<span class="expire-value expire-minutes">' . esc_html( $remain_minutes ) . '</span>';
					$timer .= '<span class="expire-value expire-seconds">' . esc_html( $remain_seconds ) . '</span>';
				$timer .= '</a>';
			$timer .= '</p>';

		// Close up our inner div.
		$timer .= '</div>';

	// Close up our div.
	$timer .= '</div>';

	// Run our setup through a filter.
	$build  = apply_filters( Core\HOOK_PREFIX . 'timer_markup', trim( $timer ) );

	// And echo it out if requested.
	if ( $echo ) {
		echo $build; // WPCS: XSS ok.
	}

	// Just return it.
	return $build;
}

/**
 * Build out and return the expiration modal display.
 *
 * @param  boolean $echo  Whether to echo it out or return.
 *
 * @return HTML
 */
function expire_modal_display( $echo = false ) {

	// Grab the checkout URL if we have one.
	$check_page_url = wc_get_checkout_url();

	// Set an empty.
	$modal  = '';

	// Wrap the whole thing in divs.
	$modal .= '<div id="woo-cart-expire-modal-wrap-id" class="woo-cart-expire-modal-wrap">';
	$modal .= '<div class="woo-cart-expire-modal-block">';

		// Make sure to include a button to close things.
		$modal .= '<span class="woo-cart-expire-modal-close">';
			$modal .= '<i class="dashicons dashicons-dismiss" title="' . esc_html__( 'Close alert', 'woo-cart-expiration' ) . '"></i>';
		$modal .= '</span>';

		// Add the intro text.
		$modal .= '<h4 class="woo-cart-expire-modal-intro">' . esc_html__( 'Your cart is about to expire!', 'woo-cart-expiration' ) . '</h4>';

		// Add the two buttons.
		$modal .= '<p class="woo-cart-expire-modal-links">';
			$modal .= '<a href="' . esc_url( $check_page_url ) . '">' . esc_html__( 'Go to checkout now &raquo;', 'woo-cart-expiration' ) . '</a>';
		$modal .= '</p>';

	// Close up our divs.
	$modal .= '</div>';
	$modal .= '</div>';

	// Run our setup through a filter.
	$build  = apply_filters( Core\HOOK_PREFIX . 'expire_modal', trim( $modal ) );

	// And echo it out if requested.
	if ( $echo ) {
		echo $build; // WPCS: XSS ok.
	}

	// Just return it.
	return $build;
}
