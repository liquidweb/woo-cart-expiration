<?php
/**
 * Our activation call
 *
 * @package WooCartExpiration
 */

// Declare our namespace.
namespace LiquidWeb\WooCartExpiration\Activate;

// Set our aliases.
use LiquidWeb\WooCartExpiration as Core;

/**
 * Our inital setup function when activated.
 *
 * @return void
 */
function activate() {

	// Set our two settings.
	add_option( Core\OPTIONS_PREFIX . 'enabled', 'no' );
	add_option( Core\OPTIONS_PREFIX . 'mins', 15 );

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'activate_process' );

	// And flush our rewrite rules.
	flush_rewrite_rules();
}
register_activation_hook( Core\FILE, __NAMESPACE__ . '\activate' );
