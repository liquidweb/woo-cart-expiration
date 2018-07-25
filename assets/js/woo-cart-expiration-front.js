
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
			// Clear out the cart intervals and remove the timer itself.
			destroyAllEvidence();

			// Refresh the Woo cart fragments.
			jQuery( document.body ).trigger( 'wc_fragment_refresh' );

			// And be done.
			return;
		}

	// Finish up the Ajax call, enforcing the JSON setup.
	}, 'json' );

	// And that's it.
}

/**
 * Our initial call for the timer Ajax polling.
 */
function loadCartTimer( timerInterval ) {

	// Set our interval timer with an ID so we can clear it.
	window.cartPollID = setInterval( function() {

		// Call the actual polling function.
		triggerCartPolling();

	// Include the proper interval.
	}, parseInt( timerInterval, 10 ) );
}

/**
 * The visal timer portion
 */
function displayTimerValues( expireDate, timerBlock ) {

	// Set my current dates to handle comparisons.
	var currentDate = Math.floor( jQuery.now() / 1000 );

	// Compare the two dates.
	if ( parseInt( expireDate, 10 ) <= parseInt( currentDate, 10 ) ) {
		return;
	}

	// Calculate my initial seconds.
	var seconds = expireDate - currentDate;

	// See if I have any minutes.
	var minutes = Math.floor( seconds / 60 );

	// Deduct the amount of seconds if we have minutes.
	if ( minutes > 0 ) {
		seconds -= minutes * 60;
	}

	// Make sure the seconds always has 2 digits.
	seconds = ( String( seconds ).length >= 2 ) ? seconds : '0' + seconds;

	// Update the strings with our values.
	timerBlock.find( '.expire-minutes' ).text( minutes );
	timerBlock.find( '.expire-seconds' ).text( seconds );
}

/**
 * Clear out the intervals we may have set.
 */
function destroyAllEvidence() {

	// Check for the cart polling.
	if ( window.cartPollID !== undefined && window.cartPollID !== 'undefined' ) {
		window.clearInterval( window.cartPollID );
	}

	// Check for the visual polling.
	if ( window.cartTimeID !== undefined && window.cartTimeID !== 'undefined' ) {
		window.clearInterval( window.cartTimeID );
	}

	// And remove the whole counter thing.
	document.getElementById( 'woo-cart-timer-wrap' ).remove();
}

/**
 * Now let's get started.
 */
jQuery( document ).ready( function($) {

	/*
	 * Our actual countdown function.
	 */
	$.fn.countdown = function( options, callback ) {

		// Set our block variable.
		timerBlock = $( this );

		// Set the expiration date.
		expireDate = options.date;

		// Kick off the timer.
		displayTimerValues( expireDate, timerBlock );

		// Set our interval timer with an ID so we can clear it.
		window.cartTimeID = setInterval( function() {

			// Call the actual timer function.
			displayTimerValues( expireDate, timerBlock );

		// Include the proper interval.
		}, 1000 );
	};

	/*
	 * Set a few known variables.
	 */
	var cartIntervl = wooCartExpiration.interval;
	var expireDate  = 0;

	// Attempt to pull the meta tag.
	var checkExpire = document.querySelector( 'meta[name="woo-cart-expiration"]' );

	// Now check the setup once we're done loading.
	if ( checkExpire ) {

		// Get the seconds remaining.
		expireDate  = checkExpire.getAttribute( 'content' );

		// Load our timer.
		loadCartTimer( cartIntervl );

		// Call the visual counter.
		jQuery( '#woo-cart-expire-countdown' ).countdown({
			date: expireDate
		});

	// If no meta tag exists, make sure no intervals are running.
	} else {
		destroyAllEvidence();
	}

//********************************************************
// You're still here? It's over. Go home.
//********************************************************
});
