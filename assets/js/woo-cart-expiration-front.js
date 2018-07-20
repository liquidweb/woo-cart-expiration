
/**
 * Now let's get started.
 */
jQuery( document ).ready( function($) {

	$( document ).on( 'heartbeat-send', function ( event, data ) {
		// Add additional data to Heartbeat data.
		//console.log( 'send-' + Date.now() );
		//console.log( event );
		//console.log( data );
		data.foo = 'bar';
	});


	$( document ).on( 'heartbeat-tick', function ( event, data ) {
		//console.log( 'tick-' + Date.now() );
		//console.log( event );
		//console.log( data );
		if ( ! data.foo_hashed ) {
			console.log( 'got no hash' );
			return;
		}

		console.log( data.foo_hashed );
	});

//********************************************************
// You're still here? It's over. Go home.
//********************************************************
});
