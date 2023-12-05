/**
 * This solution is for wordpress problem with preview link in editor and based on https://github.com/wpengine/faustjs/blob/canary/plugins/faustwp/includes/replacement/previewlinks.js
 * TODO: Please remove this once this issue is resolved: https://github.com/WordPress/gutenberg/issues/13998
 */

document.addEventListener("DOMContentLoaded", function () {
	// Get the preview data via wp_localize_script
	const previewData = window._warpnextjs_data;

	/**
	 * Check to make sure there is a preview link before continuing, as there may not be a preview link
	 * for every instance the block editor is enqueued (e.g. /wp-admin/widgets.php)
	 */
	if (!previewData) {
		return;
	}

	const wpVersion = previewData._wp_version;
	const previewLink = previewData._preview_link;

	function debounce(func, wait) {
		let timeout;
		return function () {
			const context = this;
			const args = arguments;
			clearTimeout(timeout);
			timeout = setTimeout(function () {
				func.apply(context, args);
			}, wait);
		};
	}

	// Handle potential breaking changes from WordPress.
	function getPreviewLinksByVersion(version) {
		switch (version) {
			default:
				return {
					headerLink: document.querySelector(
						".edit-post-header-preview__grouping-external a"
					),
					snackbarLink: document.querySelector(
						".components-snackbar__content a"
					),
					previewButton: document.querySelector(
						".components-button.editor-post-preview"
					),
					toolbarLink: document.querySelector(
						"#wp-admin-bar-preview a"
					),
				};
		}
	}

	function updateUIElements() {
		const { headerLink, snackbarLink, previewButton, toolbarLink } =
			getPreviewLinksByVersion(wpVersion);

		// Clone & replace the original link in order to clear pre-existing events.
		if (headerLink && headerLink.getAttribute("href") !== previewLink) {
			const clonedHeaderLink = headerLink.cloneNode(true);
			headerLink.parentNode.replaceChild(clonedHeaderLink, headerLink);
			if (clonedHeaderLink)
				clonedHeaderLink.setAttribute("href", previewLink);
		}

		if (snackbarLink && snackbarLink.getAttribute("href") !== previewLink) {
			snackbarLink.setAttribute("href", previewLink);
		}

		if (
			previewButton &&
			previewButton.getAttribute("href") !== previewLink
		) {
			previewButton.setAttribute("href", previewLink);
		}

		if (toolbarLink && toolbarLink.getAttribute("href") !== previewLink) {
			toolbarLink.setAttribute("href", previewLink);
		}
	}

	// Run the update function on initial page load.
	const debouncedUpdateUIElements = debounce(updateUIElements, 300);

	// Observe DOM changes to update UI elements accordingly.
	const observer = new MutationObserver(debouncedUpdateUIElements);
	observer.observe(document.body, { childList: true, subtree: true });
});
