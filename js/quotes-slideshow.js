/**
 * Quotes Page Slideshow
 */

/**
 * HSL Color Calculations `calcColor`
 * JSFiddle: https://jsfiddle.net/sr4136/9k7dmhLe/
 * Based on: https://stackoverflow.com/a/36688245/2203220
 */
function calcColor( min, max, val ){
    var minHue = 300, maxHue=0;
    var curPercent = (val - min) / (max-min);
    var colString = "hsl(" + ((curPercent * (maxHue-minHue) ) + minHue) + ",100%,50%)";
    return colString;
}


/**
 * Shuffle DOM elements
 * From: https://css-tricks.com/snippets/jquery/shuffle-dom-elements/
 */
(function($){
    $.fn.shuffle = function() {
        var allElems = this.get(),
            getRandom = function(max) {
                return Math.floor(Math.random() * max);
            },
            shuffled = $.map(allElems, function(){
                var random = getRandom(allElems.length),
                    randEl = $(allElems[random]).clone(true)[0];
                allElems.splice(random, 1);
                return randEl;
           });
        this.each(function(i){
            $(this).replaceWith($(shuffled[i]));
        });
        return $(shuffled);
    };
})(jQuery);

/**
 * Shuffles array in place.
 * From: https://stackoverflow.com/a/6274381
 */
function shuffle(a) {
    var j, x, i;
    for (i = a.length - 1; i > 0; i--) {
        j = Math.floor(Math.random() * (i + 1));
        x = a[i];
        a[i] = a[j];
        a[j] = x;
    }
    return a;
}

/**
 * Get URL Parameters
 * From: https://stackoverflow.com/a/21903119
 */
var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};


/* Execution */
( function( $ ) {

	var orderby = getUrlParameter( 'orderby' );
	var layout = getUrlParameter( 'layout' );

	$( window ).load( function () {
		if( 'random' == orderby || 'slideshow' == layout ){
			$( 'article' ).shuffle();
		}
		var quotes_count = $( 'article' ).length;
		var colors_array = [];
		for ( i = 0; i <= quotes_count; i++) {
			colors_array.push( calcColor( 0, quotes_count, i ) );
		}
		shuffle( colors_array );

		$( 'article' ).each( function(){
			var article_index = $( this ).index();
			var this_color = colors_array[ article_index-1 ];
			$( this ).find( 'blockquote' ).css( 'border-color', this_color );
		} ) ;

		if( 'slideshow' == layout ){
			$( 'html' ).addClass( 'quotes-slideshow' );

			$( '#main' ).append( '<div class="quotes-progress-indicator"><span></span></div>' );

			initial_color = colors_array[ 0 ];
			$( '.quotes-progress-indicator span' ).css( 'background', initial_color );

			$( 'article' ).first().addClass( 'active' );
			
			var first_run = true;
			
			function slide(){
			
				$( '.quotes-progress-indicator span' ).css( 'width', '0%' );

				var current_quote = $( 'article.active' );
				var current_index = $( current_quote ).index();
				var slide_time = Math.round( $( current_quote ).data( 'slidetime' ) );
				console.log( slide_time );

				if( !first_run ){
					$( current_quote ).removeClass( 'active' );

					if( current_index !== quotes_count ){
						$( current_quote ).next( 'article' ).addClass( 'active' );
					}else {
						$( 'article' ).first().addClass( 'active' );
					}
				}

				$( '.quotes-progress-indicator span' ).stop().animate( {
					width: '100%'
				}, slide_time );

				var new_color = colors_array[ current_index ];
				$( '.quotes-progress-indicator span' ).css( 'background', new_color );
				
				setTimeout( slide, slide_time );
				
				first_run = false;
			}
			slide();

		}
	} ); // window.load
} )( jQuery );