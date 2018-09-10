
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

			// @@todo determine how to handle an OK.

			// And be done.
			return;
		}

		// Handle the failure.
		if ( response.success === false || response.success === 'false' ) {

			// Clear out the cart intervals and remove the timer itself.
			destroyAllEvidence();

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

			// Remove the existing timer and modal.
			removeMarkupDisplay( 'woo-cart-timer-wrap-id' );
			removeMarkupDisplay( 'woo-cart-expire-modal-wrap-id' );

			// Add our new timer and modal.
			jQuery( 'body' ).append( response.data.markup.timer );
			jQuery( 'body' ).append( response.data.markup.modal );

			// Add our new head meta tag.
			jQuery( 'head' ).append( '<meta name="woo-cart-expiration" content="' + parseInt( wooCartExpiration.set_expired, 10 ) + '" />' );

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

			// Destroy the timer and whatnot.
			destroyAllEvidence();

			// And be done.
			return;
		}

	// Finish up the Ajax call, enforcing the JSON setup.
	}, 'json' );

	// And that's it.
}

/**
 * Remove any cart contents we may have.
 */
function killTheCartContents() {

	// Build the data structure for the call.
	var data = {
		action: 'woo_cart_clear_expired_cart',
		nonce: wooCartExpiration.killit_nonce,
	};

	// Send out the ajax call itself.
	jQuery.post( wooCartExpiration.ajaxurl, data, function( response ) {

		// Handle the failure.
		if ( response.success === false || response.success === 'false' ) {
			return;
		}

		// If we cleared, make sure the fragments are flushed.
		if ( response.data.cleared === true ) {

			// If we're on the checkout page, we need to redirect to cart.
			if ( 'checkout' === wooCartExpiration.maybe_check ) {
				window.location.href = wooCartExpiration.cart_url;
			} else {
				jQuery( document.body ).trigger( 'wc_fragment_refresh' );
			}
		}

	// Finish up the Ajax call, enforcing the JSON setup.
	}, 'json' );

	// And that's it.
	return;
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

		// Destroy the evidence.
		destroyAllEvidence();

		// And be done.
		return;
	}

	// Calculate my total seconds.
	var totalSeconds = expireDate - currentDate;

	// If we haz none, kill it dead with fire.
	if ( parseInt( totalSeconds, 10 ) < 1 ) {

		// Destroy the evidence.
		destroyAllEvidence();

		// And be done.
		return;
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
	var classPoints = [
		10, 30, 60, 180, 300
	];

	// Now start doing some math.
	if ( parseInt( totalSeconds, 10 ) < parseInt( classPoints[0], 10 ) ) {

		// And show the expire modal.
		jQuery( '.woo-cart-expire-modal-wrap' ).addClass( 'woo-cart-expire-modal-wrap-display' );

	} else if ( parseInt( totalSeconds, 10 ) < parseInt( classPoints[1], 10 ) ) {

		// Add our full color and pulsing classes.
		radialBlock.addClass( 'woo-cart-timer-radial-expire-full' );
		radialBlock.addClass( 'woo-cart-timer-radial-expire-pulse' );

		// And remove the circle animation.
		radialBlock.removeClass( 'woo-cart-radial-animate' );

	} else if ( parseInt( totalSeconds, 10 ) < parseInt( classPoints[2], 10 ) ) {

		// Remove any older ones.
		radialBlock.removeClass( 'woo-cart-timer-radial-expire-closer' );
		radialBlock.removeClass( 'woo-cart-timer-radial-expire-warning' );

		// And add the important one.
		radialBlock.addClass( 'woo-cart-timer-radial-expire-soon' );

	} else if ( parseInt( totalSeconds, 10 ) < parseInt( classPoints[3], 10 ) ) {

		// Remove any older ones.
		radialBlock.removeClass( 'woo-cart-timer-radial-expire-closer' );

		// And add the important one.
		radialBlock.addClass( 'woo-cart-timer-radial-expire-warning' );

	} else if ( parseInt( totalSeconds, 10 ) < parseInt( classPoints[4], 10 ) ) {

		// Check the getting closer.
		radialBlock.addClass( 'woo-cart-timer-radial-expire-closer' );
	}

	// And be done.
}

/**
 * Clear out the any and all parts of the process.
 */
function destroyAllEvidence() {

	// Stomp any intervals runnning.
	killAllIntervals();

	// Make sure the cart is cleared out first.
	killTheCartContents();

	// Remove the existing timer.
	removeMarkupDisplay( 'woo-cart-timer-wrap-id' );

	// Remove the modal.
	removeMarkupDisplay( 'woo-cart-expire-modal-wrap-id' );

	// Delete the cookie to be safe.
	document.cookie = wooCartExpiration.cookie_name + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";

	// And be done.
}

/**
 * Clear out the intervals we may have set.
 */
function killAllIntervals() {

	// Check for the cart polling.
	if ( window.cartPollID !== undefined && window.cartPollID !== 'undefined' ) {
		window.clearInterval( window.cartPollID );
	}

	// Check for the visual polling.
	if ( window.cartTimeID !== undefined && window.cartTimeID !== 'undefined' ) {
		window.clearInterval( window.cartTimeID );
	}
}

/**
 * Remove a portion of the markup.
 */
function removeMarkupDisplay( elementID ) {

	// Set a variable for the (possible) element.
	var maybeExists = document.getElementById( elementID );

	// If we have it, move it.
	if ( maybeExists ) {
		maybeExists.remove();
	}
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
		killAllIntervals();
	}

	// Check if we're on the checkout page.
	if ( 'checkout' === maybeCheck ) {

		// Handle our reset.
		resetCookieAtCheckout();

		// Kill the modal.
		removeMarkupDisplay( 'woo-cart-expire-modal-wrap-id' );
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
		$( '.woo-cart-expire-modal-block' ).fadeOut( 'slow' );

		// Perhaps kill the timer.
		maybeRemoveTimer();
	});

//********************************************************
// You're still here? It's over. Go home.
//********************************************************
});
