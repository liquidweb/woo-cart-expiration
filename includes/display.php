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
use LiquidWeb\WooCartExpiration\Markup as Markup;
use LiquidWeb\WooCartExpiration\Utilities as Utilities;

/**
 * Start our engines.
 */
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_expiration_assets' );
add_action( 'wp_head', __NAMESPACE__ . '\load_expiration_metatag', 999 );

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

	// Set my localized variables.
	$local  = array(
		'ajaxurl'      => admin_url( 'admin-ajax.php' ),
		'cart_url'     => Utilities\get_cart_url(),
		'maybe_check'  => Utilities\maybe_checkout_page(),
		'timer_nonce'  => wp_create_nonce( 'woo_cart_timer_action' ),
		'markup_nonce' => wp_create_nonce( 'woo_markup_timer_action' ),
		'count_nonce'  => wp_create_nonce( 'woo_cart_count_action' ),
		'reset_nonce'  => wp_create_nonce( 'woo_cart_reset_action' ),
		'killit_nonce' => wp_create_nonce( 'woo_cart_killit_action' ),
		'set_expired'  => Utilities\get_initial_expiration_times( 'expire' ),
		'cookie_name'  => Core\COOKIE_NAME,
		'interval'     => apply_filters( Core\HOOK_PREFIX . 'timer_interval', 10000 ), // Time in microseconds.
	);

	// Load our CSS file.
	wp_enqueue_style( $handle, Core\ASSETS_URL . '/css/' . $file . '.css', array( 'dashicons' ), $vers, 'all' );

	// And our JS.
	wp_enqueue_script( $handle, Core\ASSETS_URL . '/js/' . $file . '.js', array( 'jquery' ), $vers, true );
	wp_localize_script( $handle, 'wooCartExpiration', $local );

	// Include our action let others load things.
	do_action( Core\HOOK_PREFIX . 'after_front_asset_load' );
}

/**
 * Include some meta tags to help our expiration JS.
 *
 * @return string
 */
function load_expiration_metatag() {

	// Run our inital check.
	$expire = Utilities\get_current_cookie_expiration();

	// Bail without a cookie.
	if ( ! $expire ) {
		return;
	}

	// Load up our meta tag.
	echo '<meta name="woo-cart-expiration" content="' . absint( $expire ) . '" />';

	// And include our timer markup.
	add_action( 'wp_footer', __NAMESPACE__ . '\load_timer_display', 999 );

	// And include our modal markup, excluding the checkout page.
	if ( ! is_checkout() ) {
		add_action( 'wp_footer', __NAMESPACE__ . '\load_modal_display', 999 );
	}
}

/**
 * Output the timer markup display if loaded.
 *
 * @return void
 */
function load_timer_display() {
	Markup\timer_markup_display( true );
}

/**
 * Output the timer markup display if loaded.
 *
 * @return void
 */
function load_modal_display() {
	Markup\expire_modal_display( true );
}
