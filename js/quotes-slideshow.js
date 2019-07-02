/**
 * Quotes Page Slideshow
 */

/**
 * HSL Color Calculations `calcColor`
 * JSFiddle: https://jsfiddle.net/sr4136/9k7dmhLe/4/
 * Based on: https://stackoverflow.com/a/36688245/2203220
 */
function calcColor( min, max, val ){
    var minHue = 240, maxHue=0;
    var curPercent = (val - min) / (max-min);
    var colString = "hsl(" + ((curPercent * (maxHue-minHue) ) + minHue) + ",100%,50%)";
    return colString;
}

/* Execution */
( function( $ ) {

	$( 'html' ).addClass( 'quotes-slideshow' );

	$( window ).load( function () {

		$( '#main' ).append( '<div class="timeline"><span></span></div>' );

		var quotes_count = $( 'article' ).length;
		var colors_array = [];
		for ( i = 0; i <= quotes_count; i++) {
			colors_array.push( calcColor( 0, quotes_count, i ) );
		}

		initial_color = colors_array[ 0 ];
		$( '.timeline span' ).css( 'background', initial_color );

		$( 'article' ).each( function(){
			var article_index = $( this ).index();
			var this_color = colors_array[ article_index-1 ];
			$( this ).find( 'blockquote' ).css( 'border-color', this_color );
		} ) ;

		$( 'article' ).first().addClass( 'active' );

		$( '.timeline span' ).stop().animate( {
			width: '100%'
		}, 30000 );

		function slide(){

			$( '.timeline span' ).css( 'width', '0%' );

			var current_quote = $( 'article.active' );
			var current_index = $( current_quote ).index();

			console.log( quotes_count + ' -- ' + current_index );

			$( current_quote ).removeClass( 'active' );

			if( current_index !== quotes_count ){
				$( current_quote ).next( 'article' ).addClass( 'active' );
			}else {
				$( 'article' ).first().addClass( 'active' );
			}

			$( '.timeline span' ).stop().animate( {
				width: '100%'
			}, 30000 );

			var new_color = colors_array[ current_index ];
			$( '.timeline span' ).css( 'background', new_color );
		}

		setInterval( slide, 30000 );

	} );
} )( jQuery );