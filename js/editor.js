/**
 * Block Editor: Apply custom body classes to the editor canvas iframe.
 *
 * Modern Gutenberg renders the editor inside a sandboxed iframe. Classes
 * set via the "Body Class" metabox (see `/inc/metabox-body-class.php`) are
 * available via the `iframeBodyData` global localised by PHP, but they must
 * be manually applied to the iframe document after it mounts.
 *
 * The iframe starts as `about:blank`, then loads a blob URL. Its `<body>`
 * may not exist immediately after the `load` event, so we observe
 * `documentElement` as a fallback until the body is available.
 *
 * @see /inc/metabox-body-class.php
 * @via https://wordpress.stackexchange.com/a/427560
 */

wp.domReady( () => {
	// Matches both the legacy `name` attribute and the newer `title` attribute
	// used by Gutenberg across different WordPress versions.
	const IFRAME_SEL = 'iframe[name="editor-canvas"], iframe[title="Editor canvas"]';
	const classes = iframeBodyData.classes.split( ' ' );

	// Guard against applying classes more than once (e.g. if the observer fires multiple times).
	let applied = false;

	/**
	 * Add the custom classes to the iframe body and .editor-styles-wrapper.
	 * Returns true if classes were applied, false if conditions weren't met.
	 */
	function applyClasses( doc ) {
		if ( applied || ! doc || ! doc.body ) return false;
		doc.body.classList.add( ...classes );
		// Also target .editor-styles-wrapper so CSS selectors scoped to it work too.
		doc.querySelector( '.editor-styles-wrapper' )?.classList.add( ...classes );
		applied = true;
		return true;
	}

	/**
	 * Attempt to apply classes to a ready iframe document.
	 * If .editor-styles-wrapper hasn't rendered yet, observe the body until it does.
	 */
	function tryIframe( iframe ) {
		const doc = iframe?.contentDocument;
		// Skip if the document isn't ready or is still the initial blank page.
		if ( ! doc || doc.URL === 'about:blank' || ! doc.body ) return false;
		if ( applyClasses( doc ) ) return true;
		// Body exists but .editor-styles-wrapper not yet in the DOM — wait for it.
		const obs = new MutationObserver( ( m, o ) => { if ( applyClasses( doc ) ) o.disconnect(); } );
		obs.observe( doc.body, { childList: true, subtree: true } );
		return true;
	}

	/**
	 * Called after the iframe `load` event fires.
	 * The blob URL document may fire `load` before `<body>` is parsed,
	 * so we observe `documentElement` as a fallback.
	 */
	function onIframeLoad( iframe ) {
		const doc = iframe.contentDocument;
		if ( ! doc ) return;
		if ( doc.body ) {
			tryIframe( iframe );
		} else {
			// `<body>` not yet available — watch for it to be inserted.
			const obs = new MutationObserver( ( m, o ) => {
				if ( doc.body ) { o.disconnect(); tryIframe( iframe ); }
			} );
			obs.observe( doc.documentElement, { childList: true } );
		}
	}

	/**
	 * Attach to a known iframe element.
	 * Try immediately; fall back to waiting for the `load` event.
	 */
	function attachToIframe( iframe ) {
		if ( ! tryIframe( iframe ) ) {
			iframe.addEventListener( 'load', () => onIframeLoad( iframe ), { once: true } );
		}
	}

	// If the editor iframe is already in the DOM when the script runs, use it directly.
	const existing = document.querySelector( IFRAME_SEL );
	if ( existing ) {
		attachToIframe( existing );
	} else {
		// Otherwise, watch the parent document for the iframe to be inserted.
		const root = document.body || document.documentElement;
		if ( ! root ) return;
		const obs = new MutationObserver( ( m, o ) => {
			const iframe = document.querySelector( IFRAME_SEL );
			if ( ! iframe ) return;
			o.disconnect();
			attachToIframe( iframe );
		} );
		obs.observe( root, { childList: true, subtree: true } );
	}
} );



