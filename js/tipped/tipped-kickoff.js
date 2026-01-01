/* Kicking off Tipped.js
 * https://tippedjs.com
 */
(function ($) {
	Tipped.delegate(
		'.wp-block-table i, .sr-bu-timeline .timeline i, #sr-table-search--show-row-counter i',
		function (element) {
			if ($(element).data('title')) {
				return {
					content: $(element).data('title'),
				};
			}
		},
		{
			maxWidth: 300,
			size: 'large',
		}
	);
})(jQuery);
