( function( $ ){


	$( '.legend li' ).on( 'click', function(){
		var clicked = jQuery( this ).find( 'i' ).data( 'title' );

		$( '.none-found' ).remove();
		$( '.legend li.active' ).removeClass( 'active' );

		$( this ).addClass( 'active' );

		if( 0 == $( '.legend .show-all' ).length ){
			$( '<li class="show-all"><i data-title="Show All" class="fa fa-window-close"></i>Show All</li>' ).appendTo( '.legend' );
		}

		$( 'table tbody tr' ).each( function(){
			var compare = $( this ).find( 'i' ).data( 'title' );

			if( compare !== clicked ){
				$( this ).addClass( 'hidden' );
			}else {
				$( this ).removeClass( 'hidden' );
			}
		} );

		$( 'table tbody' ).each( function(){
			var rows = jQuery( this ).find( 'tr' ).not( '.hidden' );

			if( 0 == rows.length ){
				$( this ).parent( 'table' ).after( '<div class="none-found">None this year.</div>' );
			}
		} );
	} );

	$( '.legend' ).on( 'click', '.show-all', function() {
		$( this ).remove();
		$( 'table tbody tr' ).removeClass( 'hidden' );
		$( '.legend li' ).removeClass( 'active' );
		$( '.none-found' ).remove();
	} );

} )( jQuery );