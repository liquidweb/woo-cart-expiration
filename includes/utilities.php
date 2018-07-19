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

/**
 * Check to see if the expiration is indeed enabled.
 *
 * @return boolean
 */
function maybe_expiration_enabled() {

	// Pull the option.
	$enable = get_option( Core\OPTIONS_PREFIX . 'enabled', 'no' );

	// Fetch the amount of time.
	$minute = get_option( Core\OPTIONS_PREFIX . 'mins', 15 );

	// Return a simple true / false.
	return 'yes' === sanitize_text_field( $enable ) ? true : false;
}

/**
 * Calculate the expiration time for a new cookie.
 *
 * @return integer  The time, in seconds.
 */
function get_expiration_time() {

	// Fetch the amount of time.
	$stored = get_option( Core\OPTIONS_PREFIX . 'mins', 15 );

	// Set our amount of time to expire.
	return time() + ( absint( $stored ) * MINUTE_IN_SECONDS );
}
