/**
 * Block Editor: Add placeholder text to empty table block cells.
 *
 * Empty cells in supported table block styles are marked with
 * `sr-table-cell-empty` so CSS can render pseudo-placeholder labels.
 *
 * Modern Gutenberg renders the editor inside a sandboxed iframe, so this
 * script must locate the iframe, wait for its document and body to be
 * available, then observe mutations to keep placeholders in sync as the
 * user types.
 *
 * Placeholder/background companion CSS lives in `css/admin-blocks.css`.
 *
 * @see /inc/enqueue-scripts-styles.php
 */

wp.domReady( () => {
    const CELL_SELECTOR =
        '.is-style-stever-table-pro-dev td .block-editor-rich-text__editable, .is-style-stever-table-books td .block-editor-rich-text__editable';
    const BOUND_FLAG = '__srPlaceholdersBound';
    const processedDocs = new WeakSet();

    /**
     * Attach to a fully-loaded iframe document.
     * Runs an initial pass then observes mutations to update placeholders
     * whenever the user types or the block tree changes.
     */
    const attachToDocument = ( doc ) => {
        if ( ! doc || ! doc.body || processedDocs.has( doc ) ) {
            return;
        }

        processedDocs.add( doc );

        const update = () => {
            doc.querySelectorAll( CELL_SELECTOR ).forEach( ( cell ) => {
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

    const attachToIframe = ( iframe ) => {
        if ( ! iframe || iframe[ BOUND_FLAG ] ) {
            return;
        }
        iframe[ BOUND_FLAG ] = true;

        const tryAttach = () => {
            const doc = iframe.contentDocument;
            if ( ! doc ) {
                return;
            }

            // Ignore transitional about:blank docs and wait for real editor content.
            if ( doc.URL === 'about:blank' ) {
                return;
            }

            if ( doc.body ) {
                attachToDocument( doc );
            } else if ( doc.documentElement ) {
                const obs = new MutationObserver( ( m, o ) => {
                    if ( doc.body ) {
                        o.disconnect();
                        attachToDocument( doc );
                    }
                } );
                obs.observe( doc.documentElement, { childList: true } );
            }
        };

        // Run immediately in case iframe is already ready.
        tryAttach();
        // Keep listening because Gutenberg can reload the iframe document.
        iframe.addEventListener( 'load', tryAttach );
    };

    // Attach to any iframe present now.
    document.querySelectorAll( 'iframe' ).forEach( attachToIframe );

    // Observe for iframe insertions across Gutenberg updates.
    const root = document.body || document.documentElement;
    if ( ! root ) {
        return;
    }

    const obs = new MutationObserver( ( mutations ) => {
        mutations.forEach( ( mutation ) => {
            mutation.addedNodes.forEach( ( node ) => {
                if ( node?.nodeType !== 1 ) {
                    return;
                }

                if ( node.matches?.( 'iframe' ) ) {
                    attachToIframe( node );
                    return;
                }

                node.querySelectorAll?.( 'iframe' ).forEach( attachToIframe );
            } );
        } );
    } );

    obs.observe( root, { childList: true, subtree: true } );
} );
