// Wait for the DOM to fully load before executing the script
document.addEventListener('DOMContentLoaded', () => {
	// Class to handle table search functionality
	class TableSearch {
		constructor() {
			// Initialize DOM elements related to the search functionality
			this.sr_ts_container = document.getElementById(
				'sr-table-search--container'
			);
			this.sr_ts_wrapper = document.getElementById(
				'sr-table-search--wrapper'
			);
			this.sr_ts_input = document.getElementById(
				'sr-table-search--input'
			);
			this.sr_ts_button = document.getElementById(
				'sr-table-search--button'
			);
			this.sr_ts_count_total = document.getElementById(
				'sr-table-search--count-total'
			);
			this.sr_ts_count_showing = document.getElementById(
				'sr-table-search--count-showing'
			);
			this.sr_ts_row_counter = document.getElementById(
				'sr-table-search--show-row-counter'
			);
			this.sr_ts_tablesets = document.querySelectorAll(
				'.sr-table-sets tbody'
			);
			this.sr_ts_headings =
				document.querySelectorAll('.wp-block-heading');
			this.sr_ts_tables = document.querySelectorAll('.wp-block-table');
			this.sr_ts_rows = document.querySelectorAll(
				'.wp-block-table tbody tr'
			);
			this.sr_ts_chart_optional =
				document.getElementById('sr-chart-wrapper');
			this.sr_tl_legend_optional = document.getElementById(
				'sr-legend--container'
			);

			// Initialize the search functionality
			this.init();
		}

		// Initialize counts and event listeners
		init() {
			this.setInitialCounts(); // Set initial counts for total and showing rows
			this.addEventListeners(); // Add event listeners for user interactions
			this.checkLocalStorage(); // Check local storage for saved settings
			this.checkQueryParams(); // Check query params for search/mark
		}

		// Set the initial counts for total and currently showing rows
		setInitialCounts() {
			const totalRowCount = this.sr_ts_rows.length; // Get total number of rows
			this.sr_ts_count_showing.textContent = totalRowCount; // Display total showing count
			this.sr_ts_count_total.textContent = totalRowCount; // Display total count
		}

		// Add event listeners for various user interactions
		addEventListeners() {
			// Listen for input in the search box and debounce the search function
			this.sr_ts_input.addEventListener(
				'input',
				this.debounce(this.handleSearchInput.bind(this), 300)
			);
			// Listen for click on the clear search button
			this.sr_ts_button.addEventListener(
				'click',
				this.clearSearch.bind(this)
			);
			// Listen for click on the row counter toggle
			this.sr_ts_row_counter.addEventListener(
				'click',
				this.toggleRowCounter.bind(this)
			);
			// Listen for focus on the search input to scroll to it
			this.sr_ts_input.addEventListener(
				'focus',
				this.scrollToSearchBar.bind(this)
			);
			this.sr_ts_tablesets.forEach((tableSet) => {
				// Listen for mouseup and touchend events to wrap selected text
				tableSet.addEventListener(
					'mouseup',
					this.wrapSelection.bind(this)
				);
				tableSet.addEventListener(
					'touchend',
					this.wrapSelection.bind(this)
				);

				// Listen for mousedown and touchstart events to unwrap selected text
				tableSet.addEventListener(
					'mousedown',
					this.unwrapSelection.bind(this)
				);
				tableSet.addEventListener(
					'touchstart',
					this.unwrapSelection.bind(this)
				);
			});
		}

		// Debounce function to limit the rate of function execution
		debounce(func, delay) {
			let timeout; // Variable to hold the timeout ID
			return function (...args) {
				clearTimeout(timeout); // Clear the previous timeout
				timeout = setTimeout(
					() => func.apply(this, args), // Execute the function after the delay
					delay
				);
			};
		}

		// Scroll to the search bar with an offset based on the presence of the admin bar
		scrollToSearchBar() {
			const offset = document.getElementById('wpadminbar') ? 48 : 24; // Determine offset
			const searchPos =
				this.sr_ts_container.getBoundingClientRect().top +
				window.scrollY -
				offset; // Calculate position
			fluidScroll({ yPos: searchPos, duration: 500 }); // Smooth scroll to the search bar
		}

		// Check local storage for saved row counter state
		checkLocalStorage() {
			if (localStorage.getItem('sr-table-row-counter') == 1) {
				this.sr_ts_row_counter.classList.add('active'); // Activate row counter if saved
				document.body.classList.add('sr-table-row-counter'); // Add class to body
			}
		}

		// Check query params
		checkQueryParams() {
			const urlParams = new URLSearchParams(window.location.search);
			const markValue = urlParams.get('m');
			const searchValue = urlParams.get('q');
			const legendValue = urlParams.get('l');

			// console.log(
			// 	'mark (m), search (q), legend (l)',
			// 	markValue,
			// 	searchValue,
			// 	legendValue
			// );

			if (searchValue) {
				const searchInput = document.getElementById(
					'sr-table-search--input'
				); // Get the search input element
				searchInput.value = searchValue;
				const tempEvent = new Event('input', {
					bubbles: true,
					cancelable: true,
				});
				searchInput.dispatchEvent(tempEvent); // Dispatch an input event to trigger search
				searchInput.focus(); // Focus on the search input
				searchInput.blur(); // Remove focus from the search input
				if (window.tableSearchInstance) {
					window.tableSearchInstance.unwrapSelection(); // Unwrap any active selections
				}
			}
			if (markValue) {
				this.handleMarking(markValue);
			}
		}

		// Handle input in the search box
		handleSearchInput() {
			// Clear tooltips, if they are open.
			tableSearchInstance.unwrapSelection();

			const query = this.sr_ts_input.value.trim(); // Get the trimmed input value
			this.sr_ts_button.classList.toggle('fa-search', !query); // Toggle search icon based on input
			this.sr_ts_button.classList.toggle('fa-times', query); // Toggle clear icon based on input
			if (this.sr_ts_chart_optional) {
				this.sr_ts_chart_optional.classList.toggle('hidden', query); // Hide chart if there is a query
			}
			if (this.sr_tl_legend_optional) {
				this.sr_tl_legend_optional.classList.toggle(
					'visually-hidden',
					query
				); // Hide legend if there is a query
			}
			// Process the search if there is a query, otherwise clear the search
			query ? this.processSearch(query) : this.clearSearch();
		}

		handleMarking(markValue) {
			//console.log(markValue);

			this.sr_ts_tables.forEach((table) => {
				const rows = table.querySelectorAll('tbody tr');
				rows.forEach((row) => {
					//console.log(rows);
					const searchStr = this.getSearchString(row); // Get the search string for the row
					const isMarked = searchStr
						.toLowerCase()
						.includes(markValue.toLowerCase()); // Check if the row matches the query
					row.classList.toggle('marked', isMarked); // Toggle visibility of the row
				});
			});
		}

		// Process the search based on the input query
		processSearch(query) {
			this.sr_ts_tables.forEach((table) => {
				const tableHeading = table.previousElementSibling; // Get the heading of the current table
				const rows = table.querySelectorAll('tbody tr'); // Get all rows in the table
				let visibleCount = 0; // Counter for visible rows

				tableHeading.classList.remove('hidden'); // Show the table heading
				table.classList.remove('hidden'); // Show the table

				// Iterate through each row to determine visibility based on the search query
				rows.forEach((row) => {
					const searchStr = this.getSearchString(row); // Get the search string for the row
					const isVisible = searchStr
						.toLowerCase()
						.includes(query.toLowerCase()); // Check if the row matches the query
					row.classList.toggle('hidden', !isVisible); // Toggle visibility of the row
					if (isVisible) visibleCount++; // Increment visible count if the row is visible
				});

				// Hide the table and heading if no rows are visible
				if (visibleCount === 0) {
					tableHeading.classList.add('hidden');
					table.classList.add('hidden');
				}
			});

			this.updateCounts(); // Update the counts after processing the search
		}

		// Get the search string for a specific row
		getSearchString(row) {
			let searchStr = row.textContent; // Start with the row's text content
			const icon = row.querySelector('i.fas'); // Check for an icon in the row
			if (icon) {
				searchStr += ' ' + icon.dataset.title; // Append the icon's title if it exists
			}
			return searchStr; // Return the complete search string
		}

		// Update the counts displayed for the search results
		updateCounts() {
			const currentShowing = document.querySelectorAll(
				'.wp-block-table tbody tr:not(.hidden)'
			).length; // Count visible rows
			this.sr_ts_count_showing.textContent = currentShowing; // Update the showing count
			this.sr_ts_container.classList.toggle(
				'zero-results',
				currentShowing === 0
			); // Add class if no results
		}

		// Clear the search input and reset the display
		clearSearch() {
			this.sr_ts_button.classList.replace('fa-times', 'fa-search'); // Change button icon to search
			this.sr_ts_input.value = ''; // Clear the input value
			if (this.sr_ts_chart_optional) {
				this.sr_ts_chart_optional.classList.remove('hidden'); // Show chart again
			}
			if (this.sr_tl_legend_optional) {
				this.sr_tl_legend_optional.classList.remove('visually-hidden'); // Show legend again
			}
			// Remove hidden class from headings, tables, and rows
			this.sr_ts_headings.forEach((heading) =>
				heading.classList.remove('hidden')
			);
			this.sr_ts_tables.forEach((table) =>
				table.classList.remove('hidden')
			);
			this.sr_ts_rows.forEach((row) => row.classList.remove('hidden'));
			this.updateCounts(); // Update counts after clearing the search
		}

		// Toggle the visibility of the row counter
		toggleRowCounter() {
			document.body.classList.toggle('sr-table-row-counter'); // Toggle class on body
			const isActive = document.body.classList.contains(
				'sr-table-row-counter'
			); // Check if active
			this.sr_ts_row_counter.classList.toggle('active', isActive); // Update button state
			localStorage.setItem('sr-table-row-counter', isActive ? 1 : 0); // Save state to local storage
		}

		// Wrap selected text in a span and create a tooltip
		wrapSelection() {
			const selection = window.getSelection(); // Get the current text selection
			if (selection.rangeCount > 0) {
				const range = selection.getRangeAt(0); // Get the selected range
				const selectedText = range.toString(); // Get the selected text

				// Check if the selected text is valid and only contains text nodes
				if (selectedText.length > 0 && this.isOnlyText(range)) {
					const span = document.createElement('span'); // Create a new span element
					span.id = 'sr-active-selection'; // Set the ID for the span
					span.textContent = selectedText; // Set the text content of the span

					range.deleteContents(); // Remove the selected text from the document
					range.insertNode(span); // Insert the new span in place of the selected text
					this.createTooltip(span, selectedText); // Create a tooltip for the selected text
				}
			}
		}

		// Check if the selected range contains only text nodes
		isOnlyText(range) {
			const startContainer = range.startContainer; // Get the start container of the range
			const endContainer = range.endContainer; // Get the end container of the range
			return (
				startContainer.nodeType === Node.TEXT_NODE && // Ensure start is a text node
				endContainer.nodeType === Node.TEXT_NODE && // Ensure end is a text node
				(startContainer === endContainer || // Check if both ends are the same
					range.commonAncestorContainer === startContainer.parentNode) // Check if they share a common parent
			);
		}

		// Create a tooltip for the selected text
		createTooltip(span, selectedText) {
			const tooltip = Tipped.create(
				span,
				() => ({
					content: `<div id="sr-send-to-search" onClick="sendToSearch(event)" data-string="${selectedText.trim()}">Search "${selectedText.trim()}"</div>`, // Tooltip content with search action
				}),
				{
					maxWidth: 300, // Set maximum width for the tooltip
					size: 'large', // Set size of the tooltip
					position: 'bottom', // Position the tooltip below the selected text
					hideOn: false, // Do not hide on mouse out
					onShow: (content) => content.classList.add('tdp-sr'), // Add class when shown
				}
			);
			Tipped.show(span); // Show the tooltip
			Tipped.refresh(span); // Refresh the tooltip to ensure it displays correctly
		}

		// Unwrap the selected text and remove the tooltip
		unwrapSelection() {
			Tipped.remove('#sr-active-selection'); // Remove the tooltip for the active selection
			const activeSelections = document.querySelectorAll(
				'#sr-active-selection'
			); // Get all active selections

			// Replace each active selection span with its text content
			activeSelections.forEach((span) => {
				const textContent = span.textContent; // Get the text content of the span
				const parent = span.parentNode; // Get the parent node of the span
				parent.replaceChild(
					document.createTextNode(textContent), // Replace span with a text node
					span // Remove the span
				);
			});
		}
	}

	// Initialize the TableSearch class
	window.tableSearchInstance = new TableSearch();

	// Send string from tooltip to search box when the tooltip action is clicked
	window.sendToSearch = function (event) {
		tableSearchInstance.sr_ts_wrapper.classList.remove('visually-hidden'); // Make sure the search input is visible
		if (event.target.dataset.string) {
			const searchInput = document.getElementById(
				'sr-table-search--input'
			); // Get the search input element
			searchInput.value = event.target.dataset.string; // Set the input value to the selected text
			const tempEvent = new Event('input', {
				bubbles: true,
				cancelable: true,
			});
			searchInput.dispatchEvent(tempEvent); // Dispatch an input event to trigger search
			searchInput.focus(); // Focus on the search input
			searchInput.blur(); // Remove focus from the search input
			tableSearchInstance.unwrapSelection(); // Unwrap any active selections
		}
	};

	// A check for if a string contains markup
	function containsMarkup(str) {
		const markupPattern = /<\/?[\w\s="/.':;#-\/\?]+>/; // Regex pattern to detect HTML tags
		return markupPattern.test(str); // Return true if the string contains markup
	}
});
