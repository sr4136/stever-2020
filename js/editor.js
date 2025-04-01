/* Adds body class to block editor iframe.
 * See: `/inc/metabox-body-class.php`
 * @via: https://wordpress.stackexchange.com/a/427560
 */

wp.domReady(() => {
	// Attempt adding classes immediately if iframe is already available
	function addClassesToIframeBody() {
		const editorIframe = document.querySelector('iframe');

		if (editorIframe) {
			const editorBody = editorIframe.contentDocument.querySelector(
				'.editor-styles-wrapper'
			);

			if (editorBody) {
				editorBody.classList.add(...iframeBodyData.classes.split(' '));
				console.log('Classes added to iframe body');
				return true;
			}
		}
		return false;
	}

	if (!addClassesToIframeBody()) {
		// Observe `document.body` for the iframe addition
		const observer = new MutationObserver(() => {
			const editorIframe = document.querySelector('iframe');

			if (editorIframe) {
				const editorBody = editorIframe.contentDocument.querySelector(
					'.editor-styles-wrapper'
				);

				if (editorBody) {
					if (addClassesToIframeBody()) {
						observer.disconnect();
						console.log(
							'Stopped observing - iframe body found and modified'
						);
					}
				} else {
					console.log('No body (.editor-styles-wrapper) found');
				}
			}
		});

		console.log('Starting MutationObserver on document.body');
		observer.observe(document.body, { childList: true, subtree: true });
	}
});
