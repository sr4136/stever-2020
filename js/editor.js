/**
 * Block Editor: Apply custom body classes to the editor canvas iframe.
 *
 * Classes from the "Body Class" metabox are passed from PHP via
 * `iframeBodyData` and applied from inside the `editor-canvas` iframe.
 *
 * The `.editor-styles-wrapper` element may not exist on the first frame, so
 * this script retries for a short, bounded period and then stops.
 *
 * @see /inc/metabox-body-class.php
 */

(() => {
	const data = window.iframeBodyData || {};
	const classes = ( data.classes || '' ).split( /\s+/ ).filter( Boolean );
	const isEditorCanvas = window.frameElement?.getAttribute( 'name' ) === 'editor-canvas';

	if ( ! isEditorCanvas || classes.length === 0 ) {
		return;
	}

	const apply = () => {
		if ( document.body ) {
			document.body.classList.add( ...classes );
		}

		const wrapper = document.querySelector( '.editor-styles-wrapper' );
		if ( wrapper ) {
			wrapper.classList.add( ...classes );
			return true;
		}

		return false;
	};

	const run = () => {
		let attempts = 0;
		const maxAttempts = 120;

		const tick = () => {
			if ( apply() ) {
				return;
			}

			attempts += 1;
			if ( attempts < maxAttempts ) {
				window.requestAnimationFrame( tick );
			}
		};

		tick();
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', run, { once: true } );
	} else {
		run();
	}
})();



