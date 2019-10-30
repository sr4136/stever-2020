/* Customizations to the "WP Google Maps" Plugin.
 * To be entered into the "Advanced > JS" tab on https://steverudolfi.com/wp-admin/admin.php?page=wp-google-maps-menu-settings.
 *
 * 1. HTML Entity Decode
 * 2. Polylines: Bind the Add/Remove Hover Events
 * 3. Listen for Zoom Level Change
 * 4. Text Input to move the map to location
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
	
		/* 2. Polylines: Bind the Add/Remove Hover Events
		 */
		function bind_polyline_events( polyline, data ){
			google.maps.event.addListener( polyline, 'mouseover', function( event ) {
				if( zoom_level >= 10 ){
					var mouseX = event.wa.pageX;
					var mouseY = event.wa.pageY+10;
					$( 'body' ).append( '<div class="stever-map-tt" style="top: ' + mouseY + 'px; left: ' + mouseX + 'px;">' + decodeHtml( data.polyname ) + "</div>" );
				}
			});
			google.maps.event.addListener( polyline, 'mouseout', function( event ) {
				$( '.stever-map-tt' ).remove();
			});
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
						if( status == google.maps.GeocoderStatus.OK ) {
							theMap.setCenter( results[0].geometry.location );
							theMap.setZoom( 12 );
						} else {
							alert( 'Geocode was not successful for the following reason: ' + status );
						}
					} );
				}
			} );
		}
	} );
		
})( jQuery );