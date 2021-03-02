/* Customizations to the "WP Google Maps" Plugin.
 * To be entered into "Admin > Maps > Settings > Advanced Settings > Custom JS box"
 *
 * 0. *NOTE* Assuming always only one map per page.
 * 1. Text Input to move the map to location
 * 2. Polylines: Bind the Infowindow Event
 * 3. InfoWindows: add oembeds
 */

(function( $ ) {
	$( window ).on( 'load', function( event ) {
		setTimeout( function(){
			
			var theMap = window.WPGMZA.maps[0];
		
			/* 1. Text Input to move the map to location */
			var theAddressInput = document.getElementById( 'stever-mapfilter' );

			if( typeof( theAddressInput ) != 'undefined' && theAddressInput != null ){
				theAddressInput.addEventListener( 'keydown', function( e ){
					if( 13 ===  e.keyCode ){
						var address = theAddressInput.value;
						var geocoder = new google.maps.Geocoder();

						geocoder.geocode( { 'address' : address }, function( results, status ) {
							if( status === google.maps.GeocoderStatus.OK ) {

								// With bounds means it's more of a city/state/country search.
								if( results[0].geometry.bounds ){
									theMap.googleMap.fitBounds( results[0].geometry.bounds );
									
								// Without bounds means it's more of an exact address/POI.
								}else {
									theMap.setCenter( results[0].geometry.location );
									theMap.setZoom( 12 );
								}
								
							} else {
								alert( 'Geocode was not successful for the following reason: ' + status );
							}
						} );
					}
				} );
			}
			
			
			/* 2. Polylines: Bind the Infowindow Event */
			var all_polylines = theMap.polylines;
			
			all_polylines.forEach( function( item, index ) {
				google.maps.event.addListener( item.googlePolyline, 'click', function( event ) {

					/* TODO:
					 * The infowindow object coming from WPGMZA is mysterious...
					 * so I use my own infowindow object...
					 * but that means closing mine vs theirs is tricky.
					 */
					jQuery( '.gm-style-iw button' ).click();

					infoWindow = new google.maps.InfoWindow({
						content: item.polyname,
						position: event.latLng,
						map: theMap.googleMap
					});
					infoWindow.open( theMap.googleMap, item );
				} );
			} );
		

		}, 2000 ); // end setTimeout
	} ); // end windowOnLoad
})( jQuery );