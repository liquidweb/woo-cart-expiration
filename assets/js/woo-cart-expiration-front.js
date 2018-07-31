
/**
 * Our Ajax call to check against the cookie.
 */
function triggerCartPolling( cartPollID ) {

	// Build the data structure for the call.
	var data = {
		action: 'woo_cart_expiration_timer',
		nonce: wooCartExpiration.timer_nonce,
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
			jQuery( document.body ).trigger( 'updated_wc_div' );

			// And be done.
			return;
		}

	// Finish up the Ajax call, enforcing the JSON setup.
	}, 'json' );

	// And that's it.
}

/**
 * Fetch and display our timer markup.
 */
function displayTimerMarkup( cartIntervl ) {

	// Build the data structure for the call.
	var data = {
		action: 'woo_cart_get_timer_markup',
		nonce: wooCartExpiration.markup_nonce,
	};

	// Send out the ajax call itself.
	jQuery.post( wooCartExpiration.ajaxurl, data, function( response ) {

		// Handle the failure.
		if ( response.success === false || response.success === 'false' ) {
			return;
		}

		// Load markup if we have it.
		if ( response.data.markup !== '' ) {

			// Remove any existing timer markup.
			destroyAllEvidence();

			// Add our new one.
			jQuery( 'body' ).append( response.data.markup );

			// And load our timer.
			loadCartTimer( cartIntervl );

			// Call the visual counter.
			jQuery( '#woo-cart-expire-countdown' ).countdown({
				date: wooCartExpiration.set_expired,
				repeat: 1000
			});
		}

	// Finish up the Ajax call, enforcing the JSON setup.
	}, 'json' );

	// And that's it.
}

/**
 * Check to see if we need to remove the timer.
 */
function maybeRemoveTimer() {

	// Build the data structure for the call.
	var data = {
		action: 'woo_cart_check_remaining_count',
		nonce: wooCartExpiration.count_nonce,
	};

	// Send out the ajax call itself.
	jQuery.post( wooCartExpiration.ajaxurl, data, function( response ) {

		// Handle the failure.
		if ( response.success === false || response.success === 'false' ) {
			return;
		}

		// If we have zero, kill it.
		if ( response.data.empty === true ) {
			destroyAllEvidence();
		}

	// Finish up the Ajax call, enforcing the JSON setup.
	}, 'json' );

	// And that's it.
}

/**
 * Reset the timer when we hit the checkout page.
 */
function resetCookieAtCheckout() {

	// Build the data structure for the call.
	var data = {
		action: 'woo_cart_reset_cookie_checkout',
		nonce: wooCartExpiration.reset_nonce,
	};

	// Send out the ajax call itself.
	jQuery.post( wooCartExpiration.ajaxurl, data, function( response ) {

		// Handle the failure.
		if ( response.success === false || response.success === 'false' ) {
			return;
		}

		// Call the visual counter.
		jQuery( '#woo-cart-expire-countdown' ).countdown({
			date: wooCartExpiration.set_expired,
			repeat: 1000
		});

	// Finish up the Ajax call, enforcing the JSON setup.
	}, 'json' );

	// And that's it.
}

/**
 * Our initial call for the timer Ajax polling.
 */
function loadCartTimer( cartIntervl ) {

	// Set our interval timer with an ID so we can clear it.
	window.cartPollID = setInterval( function() {

		// Call the actual polling function.
		triggerCartPolling();

	// Include the proper interval.
	}, parseInt( cartIntervl, 10 ) );
}

/**
 * The visal timer portion
 */
function displayTimerValues( expireDate, timerBlock ) {

	// Set my current dates to handle comparisons.
	var currentDate = Math.floor( jQuery.now() / 1000 );

	// Compare the two dates.
	if ( parseInt( expireDate, 10 ) <= parseInt( currentDate, 10 ) ) {
		destroyAllEvidence();
	}

	// Calculate my total seconds.
	var totalSeconds = expireDate - currentDate;

	// If we haz none, kill it dead with fire.
	if ( parseInt( totalSeconds, 10 ) < 0 ) {
		destroyAllEvidence();
	}

	// Now set my initial seconds as a variable.
	var seconds = totalSeconds;

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

	// Now add our classes if need be.
	setTimerDisplayClass( totalSeconds );
}

/**
 * Add the "warning" classes as time goes down.
 */
function setTimerDisplayClass( totalSeconds ) {

	// Set the radial as a variable to use later.
	var radialBlock = jQuery( '#woo-cart-timer-wrap-id' ).find( '.woo-cart-timer-radial' );

	// Set some benchmarks.
	var bmarks = [
		10, 60, 180, 300
	];

	// Now start doing some math.
	if ( parseInt( totalSeconds, 10 ) < parseInt( bmarks[0], 10 ) ) {

		// Add our pulsing class.
		radialBlock.addClass( 'woo-cart-timer-radial-expire-pulse' );

		// And show the expire modal.
		displayExpireAlert();

	} else if ( parseInt( totalSeconds, 10 ) < parseInt( bmarks[1], 10 ) ) {

		// Remove any older ones.
		radialBlock.removeClass( 'woo-cart-timer-radial-expire-closer' );
		radialBlock.removeClass( 'woo-cart-timer-radial-expire-warning' );

		// And add the important one.
		radialBlock.addClass( 'woo-cart-timer-radial-expire-soon' );

	} else if ( parseInt( totalSeconds, 10 ) < parseInt( bmarks[2], 10 ) ) {

		// Remove any older ones.
		radialBlock.removeClass( 'woo-cart-timer-radial-expire-closer' );

		// And add the important one.
		radialBlock.addClass( 'woo-cart-timer-radial-expire-warning' );

	} else if ( parseInt( totalSeconds, 10 ) < parseInt( bmarks[3], 10 ) ) {

		// Check the getting closer.
		radialBlock.addClass( 'woo-cart-timer-radial-expire-closer' );
	}

	// And be done.
}

/**
 * Load the modal display on almost expire.
 */
function displayExpireAlert() {

	// Find the modal box and load it up.
	jQuery( '.woo-cart-expire-modal-wrap' ).addClass( 'woo-cart-expire-modal-wrap-display' );
}

/**
 * Clear out the intervals we may have set.
 */
function destroyAllEvidence() {

	// Delete the cookie to be safe.
	document.cookie = wooCartExpiration.cookie_name + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";

	// Check for the cart polling.
	if ( window.cartPollID !== undefined && window.cartPollID !== 'undefined' ) {
		window.clearInterval( window.cartPollID );
	}

	// Check for the visual polling.
	if ( window.cartTimeID !== undefined && window.cartTimeID !== 'undefined' ) {
		window.clearInterval( window.cartTimeID );
	}

	// Set a variable for the (possible) cart.
	var timerDisplay = document.getElementById( 'woo-cart-timer-wrap-id' );

	// And remove the whole counter thing.
	if ( timerDisplay ) {
		timerDisplay.remove();
	}

	// And be done.
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

		// Set the interval passed.
		expireInvl = options.repeat;

		// Kick off the timer.
		displayTimerValues( expireDate, timerBlock );

		// Set our interval timer with an ID so we can clear it.
		window.cartTimeID = setInterval( function() {

			// Call the actual timer function.
			displayTimerValues( expireDate, timerBlock );

		// Include the proper interval.
		}, parseInt( expireInvl, 10 ) );
	};

	/*
	 * Set a few known variables.
	 */
	var maybeCheck  = wooCartExpiration.maybe_check;
	var cartIntervl = wooCartExpiration.interval;
	var expireDate  = 0;

	// Attempt to pull the meta tag for our timer.
	var checkExpire = document.querySelector( 'meta[name="woo-cart-expiration"]' );

	// Now check the setup once we're done loading.
	if ( checkExpire ) {

		// Get the seconds remaining.
		expireDate  = checkExpire.getAttribute( 'content' );

		// Load our timer.
		loadCartTimer( cartIntervl );

		// Call the visual counter.
		jQuery( '#woo-cart-expire-countdown' ).countdown({
			date: expireDate,
			repeat: 1000
		});

	// If no meta tag exists, make sure no intervals are running.
	} else {
		destroyAllEvidence();
	}

	// Check if we're on the checkout page.
	if ( 'checkout' === maybeCheck ) {
		resetCookieAtCheckout();
	}

	// Check if we're on the order confirm page.
	if ( 'order' === maybeCheck ) {
		maybeRemoveTimer();
	}

	// Check for the 'add to cart' functionality.
	$( document.body ).on( 'added_to_cart', function( event, fragments, cart_hash ){

		// Load the timer markup for the first time.
		displayTimerMarkup( cartIntervl );
	});

	// Check for the 'removed from cart' functionality.
	$( document.body ).on( 'removed_from_cart', function( event ){

		// Check the cart items and remove the timer if need be.
		maybeRemoveTimer();
	});

	// Dismiss the expire notice.
	$( '.woo-cart-expire-modal-wrap' ).on( 'click', '.woo-cart-expire-modal-close', function( event ) {

		// Now fade out the message, then remove it.
		$( '.woo-cart-expire-modal-block' ).fadeOut( 'slow', function() {

			// Perhaps kill the timer.
			maybeRemoveTimer();

			// And remove the modal itself.
			$( '.woo-cart-expire-modal-wrap' ).remove();
		});
	});

//********************************************************
// You're still here? It's over. Go home.
//********************************************************
});
