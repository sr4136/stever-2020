/* Customizations to the "WP Google Maps" Plugin.
 * To be entered into the "Advanced > JS" tab on https://steverudolfi.com/wp-admin/admin.php?page=wp-google-maps-menu-settings.
 *
 * 1. HTML Entity Decode
 * 2. Bind the Add and Remove Events
 * 3. Loop through Polylines
 * 4. Listen for Zoom Level Change
 */


/* 1. HTML Entity Decode
 * From: https://stackoverflow.com/a/7394787
 */
function decodeHtml( html ) {
    var txt = document.createElement( 'textarea' );
    txt.innerHTML = html;
    return txt.value;
}

(function($) {
	// Only run on "Places I've been (map ID#1)".
	var map_id = 1;

	// Hold a 'global' zoom level.
	var zoom_level = null;
	
	/* 2. Bind the Add and Remove Events
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
	/* 3. Loop through Polylines
	 */
	$( window ).on( 'load', function( event ) {
		for( var polyline_id in WPGM_Path ){
			var polyline = WPGM_Path[ polyline_id ];
			var data = wpgmaps_localize_polyline_settings[ map_id ][ polyline_id ];
			bind_polyline_events( polyline, data );
		}
	});
	
	/* 4. Listen for Zoom Level Change
	*/
	$( document.body ).on( 'init.wpgmza', function( event ) {
		var map = event.target;
		map.on( 'zoom_changed', function( event ){
			zoom_level = map.getZoom();
		} );
	});
		
})( jQuery );