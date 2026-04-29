/**
 * Block Editor: Add placeholder text to empty table block cells.
 *
 * When editing a page with the `sr-page-table` body class, empty cells in
 * a table block show a CSS placeholder via the `sr-table-cell-empty` class.
 *
 * Modern Gutenberg renders the editor inside a sandboxed iframe, so this
 * script must locate the iframe, wait for its document and body to be
 * available, then observe mutations to keep placeholders in sync as the
 * user types.
 *
 * The companion CSS lives in `css/table-placeholder-editor.css`, loaded
 * into the editor iframe canvas via `add_editor_style()` in PHP.
 *
 * @see /inc/enqueue-scripts-styles.php
 */

wp.domReady( () => {
    // Matches both the legacy `name` attribute and the newer `title` attribute
    // used by Gutenberg across different WordPress versions.
    const IFRAME_SELECTOR = 'iframe[name="editor-canvas"], iframe[title="Editor canvas"]';

    /**
     * Attach to a fully-loaded iframe document.
     * Runs an initial pass then observes mutations to update placeholders
     * whenever the user types or the block tree changes.
     */
    const attachToDocument = ( doc ) => {
        const update = () => {
            doc.querySelectorAll(
                'body.sr-page-table .wp-block-table td .block-editor-rich-text__editable'
            ).forEach( ( cell ) => {
                // Toggle the placeholder class based on whether the cell has visible text.
                cell.classList.toggle( 'sr-table-cell-empty', ! cell.textContent.trim() );
            } );
        };
        update();
        // Watch for any content changes inside the editor canvas.
        new MutationObserver( update ).observe( doc.body, {
            childList: true,
            subtree: true,
            characterData: true,
        } );
    };

    /**
     * Called once the iframe document URL is a real blob (not `about:blank`).
     * The blob URL document can fire `load` before `<body>` is parsed,
     * so we observe `documentElement` as a fallback until the body exists.
     */
    const onIframeReady = ( doc ) => {
        if ( doc.body ) {
            attachToDocument( doc );
        } else {
            // `<body>` not yet available — wait for it to be inserted.
            const obs = new MutationObserver( ( m, o ) => {
                if ( doc.body ) { o.disconnect(); attachToDocument( doc ); }
            } );
            obs.observe( doc.documentElement, { childList: true } );
        }
    };

    /**
     * Attach a `load` listener to the editor iframe.
     * The iframe initially loads `about:blank` before the real blob URL,
     * so we skip that first load and only act on the real document.
     */
    const attachToIframe = ( iframe ) => {
        iframe.addEventListener( 'load', () => {
            const doc = iframe.contentDocument;
            if ( doc && doc.URL !== 'about:blank' ) onIframeReady( doc );
        }, { once: true } );
    };

    // If the editor iframe is already in the DOM when the script runs, use it directly.
    const iframe = document.querySelector( IFRAME_SELECTOR );
    if ( iframe ) {
        attachToIframe( iframe );
    } else {
        // Otherwise, watch the parent document for the iframe to be inserted.
        const obs = new MutationObserver( ( m, o ) => {
            const found = document.querySelector( IFRAME_SELECTOR );
            if ( found ) { o.disconnect(); attachToIframe( found ); }
        } );
        obs.observe( document.body || document.documentElement, { childList: true, subtree: true } );
    }
} );
