/* Customizations to the "WP Google Maps" Plugin.
 * To be entered into the "Advanced > JS" tab on plugin settings page.
 *
 * 1. HTML Entity Decode
 * 2. Polylines: Bind the Infowindow Event
 * 3. Listen for Zoom Level Change
 * 4. Text Input to move the map to location
 * 5. InfoWindows: add oembeds
 */


/* 1. HTML Entity Decode
 * From: https://stackoverflow.com/a/7394787
 */
function decodeHtml( html ) {
    var txt = document.createElement( 'textarea' );
    txt.innerHTML = html;
    return txt.value;
}

(function( $ ) {
	$( window ).on( 'load', function( event ) {

		// Assuming always only one map per page.
		var allMaps = window.MYMAP;
		var theMap = allMaps[ Object.keys( allMaps )[ 0 ] ].map;
		var zoom_level = null;

		/* 2. Polylines: Bind the Infowindow Event
		 */
		function bind_polyline_events( polyline, data ){
			google.maps.event.addListener( polyline, 'click', function( event ) {
				/* TODO:
				 * The infowindow object coming from WPGMZA is mysterious...
				 * so I use my own infowindow object...
				 * but that means closing mine vs theirs is tricky.
				 */
				jQuery('.gm-style-iw button').click();

				infoWindow = new google.maps.InfoWindow({
					content: data.polyname,
					position: event.latLng,
					map: theMap.googleMap
				});
				infoWindow.open( theMap.googleMap, polyline );
			} );
		}
		for( var polyline_id in WPGM_Path ){
			var polyline = WPGM_Path[ polyline_id ];
			var data = wpgmaps_localize_polyline_settings[ theMap.id ][ polyline_id ];
			bind_polyline_events( polyline, data );
		}

		/* 3. Listen for Zoom Level Change
		*/
		theMap.on( 'zoom_changed', function( event ){
			zoom_level = theMap.getZoom();
		} );


		/* 4. Text Input to move the map to location
		*/
		var theAddressInput = document.getElementById( 'stever-mapfilter' );

		if( typeof( theAddressInput ) != 'undefined' && theAddressInput != null ){
			theAddressInput.addEventListener( 'keydown', function( e ){
				if( 13 ===  e.keyCode ){
					var address = theAddressInput.value;
					var geocoder = new google.maps.Geocoder();

					geocoder.geocode( { 'address' : address }, function( results, status ) {
						if( status === google.maps.GeocoderStatus.OK ) {

							//console.log( results );

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

		/* 5. InfoWindows: add oembeds
		*/
		var markers_array = theMap.markers;
		markers_array.forEach( function( marker_obj, index ) {
			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: ( { action: 'steveroembed', steveroembedcontent: marker_obj.desc } ),
				success: function ( response ) {
					if( '0' === response.slice( -1 ) ){
						response = response.slice( 0, -1 );
						marker_obj.desc += response;
					}
				}
			} );
		} );

	} ); // end windowOnLoad
})( jQuery );
