<?php
/**
 * Plugin Name: WooCommerce Cart Expiration
 * Plugin URI:  https://github.com/liquidweb/woo-cart-expiration
 * Description: Set a time limit on a customer checking out.
 * Version:     0.2.0
 * Author:      Liquid Web
 * Author URI:  https://www.liquidweb.com
 * Text Domain: woo-cart-expiration
 * Domain Path: /languages
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * @package WooCartExpiration
 */

// Declare our namespace.
namespace LiquidWeb\WooCartExpiration;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Define our plugin version.
define( __NAMESPACE__ . '\VERS', '0.2.0' );

// Plugin root file.
define( __NAMESPACE__ . '\FILE', __FILE__ );

// Define our file base.
define( __NAMESPACE__ . '\BASE', plugin_basename( __FILE__ ) );

// Plugin Folder URL.
define( __NAMESPACE__ . '\URL', plugin_dir_url( __FILE__ ) );

// Set our assets directory constant.
define( __NAMESPACE__ . '\ASSETS_URL', URL . 'assets' );

// Set the prefix for our actions and filters.
define( __NAMESPACE__ . '\HOOK_PREFIX', 'woo_cart_expiration_' );

// Set the prefix for our options.
define( __NAMESPACE__ . '\OPTIONS_PREFIX', 'woo_cart_expiration_opt_' );

// Set the prefix for our cookie.
define( __NAMESPACE__ . '\COOKIE_NAME', 'woo_cart_expiration' );

// Set the name for our settings anchor.
define( __NAMESPACE__ . '\SETTINGS_ANCHOR', 'woo-cart-expiration-settings' );

// Load our multi-use files.
require_once __DIR__ . '/includes/cookies.php';
require_once __DIR__ . '/includes/utilities.php';
require_once __DIR__ . '/includes/markup.php';
require_once __DIR__ . '/includes/display.php';
require_once __DIR__ . '/includes/ajax-actions.php';

// Load the triggered file loads.
require_once __DIR__ . '/includes/activate.php';
require_once __DIR__ . '/includes/deactivate.php';
require_once __DIR__ . '/includes/uninstall.php';

// Load our admin side function files.
if ( is_admin() ) {
	require_once __DIR__ . '/includes/settings-tab.php';
}

// Load our front-end actions.
if ( ! is_admin() ) {
	require_once __DIR__ . '/includes/cart-actions.php';
}
