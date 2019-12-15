/* Kicking off Tipped.js
 * https://tippedjs.com
 */
( function( $ ) {
	Tipped.delegate(
		'.content-area i',
		function( element ) {
			if( $( element ).data( 'title' ) ) {
				return {
					content: $(element).data('title')
				};
			}
		},{
			maxWidth: 300,
			size: 'large'
		}
	);
} )( jQuery );
