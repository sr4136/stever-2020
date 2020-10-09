/**
 * Fancybox init script
 * Source: https://fancyapps.com/fancybox/3/docs/
 */
(function($){

	$().fancybox( {
		selector:	'.wp-block-gallery a',
		loop:		true,
		caption:	function( instance, item ) {
						var caption = $(this).siblings('figcaption').text() || '';
						return caption;
					},
	} );

} )( jQuery );