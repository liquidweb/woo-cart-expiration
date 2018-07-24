
/**
 * Our Ajax call to check against the cookie.
 */
function triggerCartPolling( cartPollID ) {

	// Build the data structure for the call.
	var data = {
		action: 'woo_cart_expiration_timer',
		nonce: wooCartExpiration.nonce,
	};

	// Send out the ajax call itself.
	jQuery.post( wooCartExpiration.ajaxurl, data, function( response ) {

		// No error, so just keep going.
		if ( response.success === true || response.success === 'true' ) {
			console.log( 'still good' );
			// @@todo determine how to handle an OK.

			// And be done.
			return;
		}

		// Handle the failure.
		if ( response.success === false || response.success === 'false' ) {
			console.log( 'expired' );
			// Clear out the cart interval.
			clearInterval( cartPollID );

			// Refresh the Woo cart fragments.
			jQuery( document.body ).trigger( 'wc_fragment_refresh' );

			// And be done.
			return;
		}

	}, 'json' );

	// And that's it.
}

/**
 * Our initial call for the timer Ajax polling.
 */
function loadCartTimer( cartPollID, timerInterval ) {

	// Set our interval timer with an ID so we can clear it.
	var cartPollID = setInterval( function() {

		// Call the actual polling function.
		triggerCartPolling( cartPollID );

	// Include the proper interval.
	}, parseInt( timerInterval, 10 ) );
}

/**
 * Now let's get started.
 */
jQuery( document ).ready( function($) {

	/*
	 * Set a few known variables.
	 */
	var cartPollID  = wooCartExpiration.setup_id;
	var cartIntervl = wooCartExpiration.interval;
	var cartRemains = 0;

	// Attempt to pull the meta tag.
	var checkExpire = document.querySelector( 'meta[name="woo-cart-expiration"]' );

	// Now check the setup once we're done loading.
	if ( checkExpire ) {

		// Get the seconds remaining.
		cartRemains = checkExpire.getAttribute( 'content' );

		// Load our timer.
		loadCartTimer( cartPollID, cartIntervl );

	// If no meta tag exists, make sure no intervals are running.
	} else {
		clearInterval( cartPollID );
	}


//********************************************************
// You're still here? It's over. Go home.
//********************************************************
});
