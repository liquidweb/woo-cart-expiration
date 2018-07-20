<?php
/**
 * Display our timer setup.
 *
 * @package WooCartExpiration
 */

// Declare our namespace.
namespace LiquidWeb\WooCartExpiration\Display;

// Set our aliases.
use LiquidWeb\WooCartExpiration as Core;
use LiquidWeb\WooCartExpiration\Utilities as Utilities;

/**
 * Start our engines.
 */
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_expiration_assets' );

add_filter( 'heartbeat_settings', __NAMESPACE__ . '\change_hearbeat_rate', 999 );
//add_filter( 'heartbeat_send', __NAMESPACE__ . '\heartbeat_send', 10, 2 );
//add_filter( 'heartbeat_nopriv_send', __NAMESPACE__ . '\heartbeat_send', 10, 2 );

add_filter( 'heartbeat_received', __NAMESPACE__ . '\heartbeat_received', 10, 2 );
add_filter( 'heartbeat_nopriv_received', __NAMESPACE__ . '\heartbeat_received', 10, 2 );


/**
 * Load our front-end side JS and CSS.
 *
 * @return void
 */
function load_expiration_assets() {

	// Check if we're enabled or not.
	$enable = Utilities\maybe_expiration_enabled();

	// Bail if we aren't enabled.
	if ( ! $enable ) {
		return;
	}

	// Set my handle.
	$handle = 'woo-cart-expiration-front';

	// Set a file suffix structure based on whether or not we want a minified version.
	$file   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'woo-cart-expiration-front' : 'woo-cart-expiration-front.min';

	// Set a version for whether or not we're debugging.
	$vers   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : Core\VERS;

	// Load our CSS file.
	//wp_enqueue_style( $handle, Core\ASSETS_URL . '/css/' . $file . '.css', false, $vers, 'all' );

	// And our JS.
	wp_enqueue_script( $handle, Core\ASSETS_URL . '/js/' . $file . '.js', array( 'jquery', 'heartbeat' ), $vers, true );
	wp_localize_script( $handle, 'wooCartExpiration',
		array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
	);

	// Include our action let others load things.
	do_action( Core\HOOK_PREFIX . 'after_front_asset_load' );
}

/**
 * Change Hearbeat rate (filter)
 *
 * Only for testing purposes
 *
 * @arg Array $settings
 *
 * @return Array
 */
function change_hearbeat_rate( $settings ) {

	$settings['interval'] = 5;

	return $settings;

}

/**
 * [heartbeat_send description]
 * @param  [type] $data      [description]
 * @param  [type] $screen_id [description]
 * @return [type]            [description]
 */
function heartbeat_send( $data, $screen_id ) {

	// preprint( $data, true );

	return wp_parse_args( array( 'foo' => 'bar' ), $data );

}


/**
 * Receive Heartbeat data and respond.
 *
 * Processes data received via a Heartbeat request, and returns additional data to pass back to the front end.
 *
 * @param array $response Heartbeat response data to pass back to front end.
 * @param array $data Data received from the front end (unslashed).
 */
function heartbeat_received( $response, $data ) {
    // If we didn't receive our data, don't send any back.
    if ( empty( $data['foo'] ) ) {
        return $response;
    }

    // Calculate our data and pass it back. For this example, we'll hash it.
    $received_data = $data['foo'];

    $response['foo_hashed'] = sha1( $received_data );
    return $response;
}

