(function () {
	/**
	 * Frontend Places Map
	 * Renders an interactive Leaflet map with toolbar controls and location search.
	 * Supports Point markers (lat/lng) and Polyline paths (encoded or raw coordinates).
	 */

	/* ===== Polyline Parsing ===== */

	/**
	 * Decode Google polyline format to lat/lng points
	 */
	function decodePolyline(encoded) {
		var points = [];
		var index = 0;
		var lat = 0;
		var lng = 0;
		var len = encoded.length;

		while (index < len) {
			var result = 0;
			var shift = 0;
			var byte;

			do {
				byte = encoded.charCodeAt(index++) - 63;
				result |= (byte & 0x1f) << shift;
				shift += 5;
			} while (byte >= 0x20);

			var dlat = result & 1 ? ~(result >> 1) : result >> 1;
			lat += dlat;

			result = 0;
			shift = 0;
			do {
				byte = encoded.charCodeAt(index++) - 63;
				result |= (byte & 0x1f) << shift;
				shift += 5;
			} while (byte >= 0x20);

			var dlng = result & 1 ? ~(result >> 1) : result >> 1;
			lng += dlng;

			points.push([lat / 1e5, lng / 1e5]);
		}

		return points;
	}

	function parsePolylineData(raw) {
		if (!raw) {
			return [];
		}

		var text = String(raw).trim();
		if (!text) {
			return [];
		}

		try {
			var parsed = JSON.parse(text);
			if (Array.isArray(parsed)) {
				return parsed
					.map(function (p) {
						if (Array.isArray(p) && p.length >= 2) {
							return [parseFloat(p[0]), parseFloat(p[1])];
						}
						if (p && typeof p === 'object' && 'lat' in p && 'lng' in p) {
							return [parseFloat(p.lat), parseFloat(p.lng)];
						}
						return null;
					})
					.filter(function (p) {
						return p && !Number.isNaN(p[0]) && !Number.isNaN(p[1]);
					});
			}
		} catch (e) {
			// Fall back to other formats.
		}

		if (text.indexOf('|') > -1) {
			return text
				.split('|')
				.map(function (part) {
					var pair = part.trim().split(',');
					if (pair.length < 2) {
						return null;
					}
					var lat = parseFloat(pair[0]);
					var lng = parseFloat(pair[1]);
					if (Number.isNaN(lat) || Number.isNaN(lng)) {
						return null;
					}
					return [lat, lng];
				})
				.filter(function (p) {
					return !!p;
				});
		}

		var tupleMatches = text.match(/\(\s*[-+]?\d+(?:\.\d+)?\s*,\s*[-+]?\d+(?:\.\d+)?\s*\)/g);
		if (tupleMatches && tupleMatches.length) {
			return tupleMatches
				.map(function (pairText) {
					var cleaned = pairText.replace(/[()]/g, '');
					var pair = cleaned.split(',');
					if (pair.length < 2) {
						return null;
					}
					var lat = parseFloat(pair[0]);
					var lng = parseFloat(pair[1]);
					if (Number.isNaN(lat) || Number.isNaN(lng)) {
						return null;
					}
					return [lat, lng];
				})
				.filter(function (p) {
					return !!p;
				});
		}

		// Try to decode as Google polyline format
		try {
			var decoded = decodePolyline(text);
			if (decoded && decoded.length > 0) {
				return decoded;
			}
		} catch (e) {
			// Not a valid polyline
		}

		return [];
	}

	/* ===== Color & UI ===== */

	var typeColorMap = {
		'General': 'var(--stever-place-type-general)',
		'Lived': 'var(--stever-place-type-lived)',
		'Layover / Pass Through': 'var(--stever-place-type-layover)',
		'Outdoors': 'var(--stever-place-type-outdoors)',
		'Indoors': 'var(--stever-place-type-indoors)',
	};

	function getTypeColor(placeType) {
		if (placeType && typeColorMap[placeType]) {
			return typeColorMap[placeType];
		}
		return 'var(--stever-place-type-default)';
	}

	function isPolylineFeature(feature) {
		return feature && (feature.dataType === 'Polyline' || feature.dataType === 'Polylines');
	}

	function getFeatureFilterKey(feature) {
		if (feature.polylineData || isPolylineFeature(feature)) {
			return 'Path';
		}

		if (feature.placeType) {
			return feature.placeType;
		}

		return 'General';
	}

	function getTypeClassName(value, fallback) {
		var normalized = String(value || '')
			.toLowerCase()
			.replace(/[^a-z0-9]+/g, '-')
			.replace(/^-+|-+$/g, '');

		if (normalized) {
			return normalized;
		}

		return fallback || 'general';
	}

	/* ===== Filter & Layer Management ===== */

	function createFilterStore(map) {
		return {
			map: map,
			groups: {},
			keys: [],
		};
	}

	function getFilterGroup(store, key) {
		if (!key) {
			return null;
		}

		if (!store.groups[key]) {
			store.groups[key] = {
				key: key,
				layerGroup: L.layerGroup().addTo(store.map),
				count: 0,
			};
			store.keys.push(key);
		}

		return store.groups[key];
	}

	/* ===== Toolbar Creation ===== */

	function createToolbar(node, map, config, filterStore) {
		var wrap = node.parentNode;
		if (!wrap) {
			return;
		}

		// Build one filter button per active feature group.
		var filters = filterStore.keys
			.map(function (key) {
				return filterStore.groups[key];
			})
			.filter(function (group) {
				return group && group.count > 0;
			});

		var generalFilters = [];
		var otherFilters = [];
		filters.forEach(function (group) {
			// Keep General first so the toolbar starts with the default view category.
			if (group && String(group.key).toLowerCase() === 'general') {
				generalFilters.push(group);
				return;
			}
			otherFilters.push(group);
		});
		filters = generalFilters.concat(otherFilters);

		if (!filters.length) {
			return;
		}

		var geocodeUrl = config.geocodeUrl || 'https://nominatim.openstreetmap.org/search';
		var geocodeEmail = config.geocodeEmail || '';
		var apiKey = config.apiKey || '';

		function isFilterActive(filter) {
			return map.hasLayer(filter.layerGroup);
		}

		function setFilterActive(filter, shouldShow) {
			var hasLayer = isFilterActive(filter);

			if (shouldShow && !hasLayer) {
				filter.layerGroup.addTo(map);
			}

			if (!shouldShow && hasLayer) {
				map.removeLayer(filter.layerGroup);
			}
		}

		var toolbar = document.createElement('div');
		toolbar.className = 'stever-places-map-toolbar';

		var searchWrap = document.createElement('div');
		searchWrap.className = 'stever-places-map-toolbar-search';

		var searchInput = document.createElement('input');
		searchInput.className = 'stever-places-map-toolbar-input';
		searchInput.type = 'text';
		searchInput.placeholder = 'Go to a location';

		var searchButton = document.createElement('button');
		searchButton.className = 'stever-places-map-toolbar-button stever-places-map-toolbar-button-search';
		searchButton.type = 'button';
		searchButton.textContent = 'Search';

		var searchStatus = document.createElement('span');
		searchStatus.className = 'stever-places-map-toolbar-status';

		function setSearchStatus(text, isError) {
			searchStatus.textContent = text;
			searchStatus.classList.toggle('is-error', !!isError);
		}

		function doGeocodeSearch() {
			var query = searchInput.value.trim();
			if (!query) {
				setSearchStatus('Enter a location first.', true);
				return;
			}

			setSearchStatus('Searching...', false);
			var url = new URL(geocodeUrl);
			url.searchParams.set('format', 'jsonv2');
			url.searchParams.set('limit', '1');
			url.searchParams.set('q', query);
			if (geocodeEmail) {
				url.searchParams.set('email', geocodeEmail);
			}
			if (apiKey) {
				url.searchParams.set('key', apiKey);
			}

			fetch(url.toString(), { method: 'GET' })
				.then(function (res) {
					if (!res.ok) {
						throw new Error('Geocode request failed');
					}
					return res.json();
				})
				.then(function (items) {
					if (!items || !items.length) {
						setSearchStatus('No location found.', true);
						return;
					}

					var item = items[0];
					if (item.boundingbox && item.boundingbox.length === 4) {
						var south = parseFloat(item.boundingbox[0]);
						var north = parseFloat(item.boundingbox[1]);
						var west = parseFloat(item.boundingbox[2]);
						var east = parseFloat(item.boundingbox[3]);
						map.fitBounds([[south, west], [north, east]], { padding: [20, 20] });
					} else {
						map.setView([parseFloat(item.lat), parseFloat(item.lon)], 11);
					}
					setSearchStatus('', false);
					console.log('Moved map to ' + (item.display_name || query) + '.');
				})
				.catch(function () {
					setSearchStatus('Search failed.', true);
				});
		}

		searchButton.addEventListener('click', doGeocodeSearch);
		searchInput.addEventListener('keydown', function (event) {
			if (event.key === 'Enter') {
				event.preventDefault();
				doGeocodeSearch();
			}
		});

		searchWrap.appendChild(searchInput);
		searchWrap.appendChild(searchButton);
		searchWrap.appendChild(searchStatus);

		var filtersWrap = document.createElement('div');
		filtersWrap.className = 'stever-places-map-toolbar-filters';

		filters.forEach(function (filter) {
			var button = document.createElement('button');
			button.className = 'stever-places-map-toolbar-button stever-places-map-toolbar-filter';
			button.type = 'button';
			button.textContent = filter.key;

			function syncButtonState() {
				button.classList.toggle('is-active', isFilterActive(filter));
			}

			button.addEventListener('click', function () {
				setFilterActive(filter, !isFilterActive(filter));
				syncButtonState();
			});

			syncButtonState();
			filtersWrap.appendChild(button);
		});

		toolbar.appendChild(searchWrap);
		toolbar.appendChild(filtersWrap);
		wrap.insertBefore(toolbar, node);
	}

	function safePopup(feature) {
		var title = String(feature.title || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		var content = String(feature.content || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		var typeClass = 'stever-place-popup-badge--' + getTypeClassName(feature.placeType, 'general');
		// Show "Path" label for polylines; show place type badge for points.
		var typeLabel = isPolylineFeature(feature) ? '<small class="stever-place-popup-type-label stever-place-popup-badge--path">Path</small>' : '';
		var typeBadge = feature.placeType ? '<small class="stever-place-popup-type-badge ' + typeClass + '">' + String(feature.placeType).replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</small>' : '';
		var editUrl = String(feature.editUrl || '');
		var editButton = '';
		var titleBlock = '<div class="stever-place-popup-title"><strong>' + title + '</strong></div>';
		var badgesBlock = (typeBadge || typeLabel) ? '<div class="stever-place-popup-badges">' + typeBadge + typeLabel + '</div>' : '';
		var notesBlock = content ? '<div class="stever-place-popup-notes">' + content + '</div>' : '';
		if (editUrl) {
			var safeEditUrl = editUrl.replace(/"/g, '&quot;');
			editButton = '<a href="' + safeEditUrl + '" class="stever-place-popup-edit-link" aria-label="Edit place"><i class="fas fa-pencil-alt"></i></a>';
		}
		return '<div class="leafleat-popup-content-wrapper">' + editButton + titleBlock + badgesBlock + notesBlock + '</div>';
	}

	/* ===== Map Initialization & Rendering ===== */

	function initMap(node) {
		if (!node || typeof L === 'undefined') {
			return;
		}

		var payload = node.getAttribute('data-places-config');
		if (!payload) {
			return;
		}

		var config;
		try {
			config = JSON.parse(payload);
		} catch (e) {
			return;
		}

		if (!config || !Array.isArray(config.features) || !config.features.length) {
			return;
		}

		var map = L.map(node).setView([20, 0], parseInt(config.zoom || 2, 10));
		
		// Build tile URL with API key parameter if provided
		var tileUrl = config.tileUrl || 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
		if (config.apiKey && config.apiKey.trim()) {
			var separator = tileUrl.indexOf('?') > -1 ? '&' : '?';
			tileUrl += separator + 'key=' + encodeURIComponent(config.apiKey);
		}
		
		L.tileLayer(tileUrl, {
			attribution: config.tileAttribution || '&copy; OpenStreetMap contributors &copy; CARTO',
			maxZoom: 19,
		}).addTo(map);

		var bounds = L.latLngBounds([]);
		var hasFeatures = false;
		var filterStore = createFilterStore(map);

		config.features.forEach(function (feature) {
			var filterKey = getFeatureFilterKey(feature);
			var filterGroup = getFilterGroup(filterStore, filterKey);
			var lat = parseFloat(feature.latitude);
			var lng = parseFloat(feature.longitude);
			var hasAnchorPoint = !Number.isNaN(lat) && !Number.isNaN(lng);

			// Polyline features render a path and optional midpoint anchor marker.
			if (feature.polylineData) {
				var points = parsePolylineData(feature.polylineData);
				if (points && points.length >= 2) {
					var poly = L.polyline(points, {
						color: '#c2552e',
						weight: 3,
						opacity: 0.8,
						lineCap: 'round',
						lineJoin: 'round',
					}).addTo(filterGroup.layerGroup);
					poly.bindPopup(safePopup(feature));
					if (hasAnchorPoint) {
						var anchorIcon = L.divIcon({
							className: 'stever-place-marker stever-place-marker-polyline-anchor',
							html: '<div style="background-color:#c2552e;"></div>',
							iconSize: [12, 12],
							iconAnchor: [6, 12],
							popupAnchor: [0, -12],
						});

						var anchorMarker = L.marker([lat, lng], {
							title: feature.title,
							icon: anchorIcon,
						}).addTo(filterGroup.layerGroup);
						anchorMarker.bindPopup(safePopup(feature));
						bounds.extend([lat, lng]);
					}
					bounds.extend(poly.getBounds());
					filterGroup.count += 1;
					hasFeatures = true;
				}
				return;
			}

			// Point features render a single marker at lat/lng.
			if (Number.isNaN(lat) || Number.isNaN(lng)) {
				return;
			}

			var color = getTypeColor(feature.placeType);
			var icon = L.divIcon({
				className: 'stever-place-marker',
				html: '<div style="background-color:' + color + ';"></div>',
				iconSize: [12, 12],
				iconAnchor: [6, 12],
				popupAnchor: [0, -12],
			});

			var marker = L.marker([lat, lng], {
				title: feature.title,
				icon: icon,
			}).addTo(filterGroup.layerGroup);
			marker.bindPopup(safePopup(feature));
			filterGroup.count += 1;
			bounds.extend([lat, lng]);
			hasFeatures = true;
		});

		if (hasFeatures && bounds.isValid()) {
			map.fitBounds(bounds, { padding: [20, 20] });
		}

		createToolbar(node, map, config, filterStore);
	}

	document.addEventListener('DOMContentLoaded', function () {
		var maps = document.querySelectorAll('.stever-places-map[data-places-config]');
		maps.forEach(initMap);
	});
})();