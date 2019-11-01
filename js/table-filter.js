( function( $ ){


	$( '.legend li' ).on( 'click', function(){
		var clicked = jQuery( this ).find( 'i' ).attr( 'title' );

		$( '.legend li.active' ).removeClass( 'active' );
		$( '.none-found' ).remove();

			console.log( 'clicked' );
			$( this ).addClass( 'active' );

			if( 0 == $( '.legend .show-all' ).length ){
				$( '<li class="show-all"><i title="Show All" class="fa fa-window-close"></i>Show All</li>' ).appendTo( '.legend' );
				$( '.show-all' ).on( 'click', function(){
					$( this ).remove();
					$( 'table tbody tr' ).removeClass( 'hidden' );
					$( '.legend li' ).removeClass( 'active' );
				} );
			}

			$( 'table tbody tr' ).each( function(){
				var compare = $( this ).find( 'i' ).attr( 'title' );

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

} )( jQuery );