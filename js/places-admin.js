(function () {
	function parseLatLngInput(value) {
		if (!value) {
			return null;
		}

		var match = value.match(/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/);
		if (!match) {
			return null;
		}

		return {
			lat: parseFloat(match[1]),
			lng: parseFloat(match[2]),
		};
	}

	function setStatus(text, isError) {
		var status = document.getElementById('stever_place_geocode_status');
		if (!status) {
			return;
		}
		status.textContent = text;
		status.style.color = isError ? '#b42318' : '#1468a0';
	}

	function setGPXStatus(text, isError) {
		var status = document.getElementById('stever_place_gpx_status');
		if (!status) {
			return;
		}
		status.textContent = text;
		status.style.color = isError ? '#b42318' : '#1468a0';
	}

	function formatPolylinePoints(points) {
		return JSON.stringify(
			points.map(function (point) {
				return {
					lat: point.lat.toFixed(8),
					lng: point.lng.toFixed(8),
				};
			})
		);
	}

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
			// Fall back to non-JSON formats.
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

		try {
			var decoded = decodePolyline(text);
			if (decoded && decoded.length > 0) {
				return decoded;
			}
		} catch (e) {
			// Not a valid encoded polyline.
		}

		return [];
	}

	function polylineMidpoint(points) {
		if (!Array.isArray(points) || points.length < 2) {
			return null;
		}

		var index = Math.floor((points.length - 1) / 2);
		if (!points[index]) {
			return null;
		}

		return {
			lat: points[index][0],
			lng: points[index][1],
		};
	}

	/**
	 * Parse GPX XML and extract track points
	 */
	function parseGPX(gpxString) {
		try {
			var parser = new DOMParser();
			var xmlDoc = parser.parseFromString(gpxString, 'text/xml');

			if (xmlDoc.getElementsByTagName('parsererror').length > 0) {
				return null;
			}

			var points = [];
			var trkpts = xmlDoc.getElementsByTagName('trkpt');

			if (!trkpts || trkpts.length === 0) {
				return null;
			}

			for (var i = 0; i < trkpts.length; i++) {
				var pt = trkpts[i];
				var lat = parseFloat(pt.getAttribute('lat'));
				var lng = parseFloat(pt.getAttribute('lon'));

				if (Number.isFinite(lat) && Number.isFinite(lng)) {
					points.push({ lat: lat, lng: lng });
				}
			}

			return points.length > 0 ? points : null;
		} catch (e) {
			return null;
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		if (typeof L === 'undefined') {
			return;
		}

		var mapEl = document.getElementById('stever-place-admin-map');
		var titleEl = document.getElementById('title');
		var latEl = document.getElementById('stever_place_latitude');
		var lngEl = document.getElementById('stever_place_longitude');
		var polylineEl = document.getElementById('stever_place_polyline_data');
		var sourceTypeField = document.getElementById('stever_place_source_type');
		var gpxBtn = document.getElementById('stever_place_gpx_import');
		var queryEl = document.getElementById('stever_place_geocode_query');
		var searchBtn = document.getElementById('stever_place_geocode_search');

		if (!mapEl || !latEl || !lngEl) {
			return;
		}

		if (titleEl && queryEl) {
			var syncQueryFromTitle = function () {
				if (document.activeElement === queryEl) {
					return;
				}

				queryEl.value = titleEl.value;
			};

			syncQueryFromTitle();
			titleEl.addEventListener('input', syncQueryFromTitle);
		}

		var settings = window.steverPlacesAdmin || {};
		var tileUrl = settings.tileUrl || 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
		var tileAttribution = settings.tileAttribution || '&copy; OpenStreetMap contributors &copy; CARTO';
		var geocodeUrl = settings.geocodeUrl || 'https://nominatim.openstreetmap.org/search';
		var geocodeEmail = settings.geocodeEmail || '';
		var apiKey = settings.apiKey || '';
		var defaultZoom = parseInt(settings.defaultZoom || '2', 10);

		var lat = parseFloat(mapEl.dataset.lat || latEl.value || '0');
		var lng = parseFloat(mapEl.dataset.lng || lngEl.value || '0');
		var hasCoords = Number.isFinite(lat) && Number.isFinite(lng) && !(lat === 0 && lng === 0);

		if (!Number.isFinite(lat)) {
			lat = 0;
		}
		if (!Number.isFinite(lng)) {
			lng = 0;
		}

		var map = L.map(mapEl).setView([lat, lng], 12);
		
		// Append API key to tile URL if provided
		if (apiKey && apiKey.trim()) {
			var separator = tileUrl.indexOf('?') > -1 ? '&' : '?';
			tileUrl += separator + 'key=' + encodeURIComponent(apiKey);
		}
		
		L.tileLayer(tileUrl, {
			attribution: tileAttribution,
			maxZoom: 19,
		}).addTo(map);

		map.on('zoomend', function () {
			console.log('stever places admin zoom:', map.getZoom());
		});

		var marker = L.marker([lat, lng], {
			draggable: true,
		}).addTo(map);
		var polylineLayer = null;

		function isPolylineSourceSelected() {
			return sourceTypeField && (sourceTypeField.value === 'Polyline' || sourceTypeField.value === 'Polylines');
		}

		function syncPolylineInputState() {
			if (!polylineEl || !sourceTypeField) {
				return;
			}

			polylineEl.disabled = !isPolylineSourceSelected();
		}

		function updateInputs(nextLat, nextLng) {
			latEl.value = Number(nextLat).toFixed(8);
			lngEl.value = Number(nextLng).toFixed(8);
		}

		if (hasCoords) {
			updateInputs(lat, lng);
		}

		marker.on('dragend', function () {
			var pos = marker.getLatLng();
			updateInputs(pos.lat, pos.lng);
			setStatus('Pin moved. Coordinates updated.', false);
		});

		function placeMarker(nextLat, nextLng, zoom) {
			marker.setLatLng([nextLat, nextLng]);
			map.setView([nextLat, nextLng], zoom || 12);
			updateInputs(nextLat, nextLng);
		}

		function applyPolylineToMap(rawPolyline, options) {
			if (!polylineEl) {
				return;
			}

			var settings = options || {};
			var points = parsePolylineData(rawPolyline);

			if (polylineLayer) {
				map.removeLayer(polylineLayer);
				polylineLayer = null;
			}

			if (points.length < 2) {
				if (settings.showInvalidMessage) {
					setGPXStatus('Polyline data is not valid yet. Add at least two points.', true);
				}
				return;
			}

			polylineLayer = L.polyline(points, {
				color: '#1468a0',
				weight: 4,
				opacity: 0.9,
			}).addTo(map);

			map.fitBounds(polylineLayer.getBounds(), {
				padding: [24, 24],
			});

			var midpoint = polylineMidpoint(points);
			if (midpoint) {
				marker.setLatLng([midpoint.lat, midpoint.lng]);
				updateInputs(midpoint.lat, midpoint.lng);
			}

			if (sourceTypeField) {
				sourceTypeField.value = 'Polyline';
				syncPolylineInputState();
			}

			if (settings.successMessage) {
				setGPXStatus(settings.successMessage, false);
			}
		}

		function doSearch() {
			var query = (queryEl && queryEl.value ? queryEl.value : '').trim();
			if (!query) {
				setStatus('Enter a location or lat,lng first.', true);
				return;
			}

			var direct = parseLatLngInput(query);
			if (direct) {
				placeMarker(direct.lat, direct.lng, 12);
				setStatus('Coordinates applied directly.', false);
				return;
			}

			setStatus('Searching...', false);
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
						setStatus('No location found.', true);
						return;
					}

					var item = items[0];
					placeMarker(parseFloat(item.lat), parseFloat(item.lon), 12);
					setStatus('Location found. Drag pin to refine.', false);
				})
				.catch(function () {
					setStatus('Search failed. Check endpoint and key settings.', true);
				});
		}

		searchBtn.addEventListener('click', doSearch);
		queryEl.addEventListener('keydown', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				doSearch();
			}
		});

		latEl.addEventListener('change', function () {
			var nextLat = parseFloat(latEl.value);
			var nextLng = parseFloat(lngEl.value);
			if (Number.isFinite(nextLat) && Number.isFinite(nextLng)) {
				placeMarker(nextLat, nextLng, map.getZoom());
			}
		});
		lngEl.addEventListener('change', function () {
			var nextLat = parseFloat(latEl.value);
			var nextLng = parseFloat(lngEl.value);
			if (Number.isFinite(nextLat) && Number.isFinite(nextLng)) {
				placeMarker(nextLat, nextLng, map.getZoom());
			}
		});

		if (sourceTypeField) {
			sourceTypeField.addEventListener('change', syncPolylineInputState);
			syncPolylineInputState();
		}

		if (gpxBtn) {
			gpxBtn.addEventListener('click', async function (e) {
				e.preventDefault();
				try {
					setGPXStatus('Reading clipboard...', false);
					var clipboardText = await navigator.clipboard.readText();

					var points = parseGPX(clipboardText);
					if (!points) {
						setGPXStatus('Invalid or empty GPX data in clipboard.', true);
						return;
					}

					if (polylineEl) {
						polylineEl.value = formatPolylinePoints(points);
						applyPolylineToMap(polylineEl.value, {
							successMessage: 'GPX imported and polyline preview updated.',
							showInvalidMessage: true,
						});
					}
				} catch (e) {
					console.error('GPX Import Error:', e);
					setGPXStatus('Clipboard access denied or error occurred.', true);
				}
			});
		}

		if (polylineEl) {
			var polylineUpdateTimer = null;

			var refreshPolylineFromField = function (showInvalidMessage) {
				applyPolylineToMap(polylineEl.value, {
					showInvalidMessage: showInvalidMessage,
				});
			};

			polylineEl.addEventListener('change', function () {
				refreshPolylineFromField(true);
			});

			polylineEl.addEventListener('paste', function () {
				window.setTimeout(function () {
					refreshPolylineFromField(true);
				}, 0);
			});

			polylineEl.addEventListener('input', function () {
				window.clearTimeout(polylineUpdateTimer);
				polylineUpdateTimer = window.setTimeout(function () {
					refreshPolylineFromField(false);
				}, 350);
			});

			if (polylineEl.value && String(polylineEl.value).trim()) {
				applyPolylineToMap(polylineEl.value, {
					showInvalidMessage: false,
				});
			}
		}
	});
})();
