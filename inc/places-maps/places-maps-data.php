<?php
/**
 * Places maps module: import/data helpers, parsing, normalization, and polyline utilities.
 */

function stever_places_clear_posts() {
	$post_ids = get_posts(array(
		'post_type' => 'stever_place',
		'post_status' => 'any',
		'fields' => 'ids',
		'posts_per_page' => -1,
	));

	foreach ($post_ids as $post_id) {
		wp_delete_post($post_id, true);
	}

	return count($post_ids);
}

function stever_places_parse_insert_values($sql) {
	$rows = array();
	$offset = 0;
	$len = strlen($sql);

	while ($offset < $len) {
		$insert_pos = stripos($sql, 'INSERT INTO', $offset);
		if (false === $insert_pos) {
			break;
		}

		$values_pos = stripos($sql, 'VALUES', $insert_pos);
		if (false === $values_pos) {
			break;
		}

		$cursor = $values_pos + 6;
		$statement = '';
		$in_quote = false;

		while ($cursor < $len) {
			$char = $sql[$cursor];
			$statement .= $char;

			if ("'" === $char) {
				$next = ($cursor + 1 < $len) ? $sql[$cursor + 1] : '';
				if ($in_quote && "'" === $next) {
					$statement .= $next;
					$cursor++;
				} else {
					$in_quote = !$in_quote;
				}
			} elseif (';' === $char && !$in_quote) {
				break;
			}

			$cursor++;
		}

		$offset = $cursor + 1;

		$tuple = '';
		$depth = 0;
		$in_quote = false;
		$s_len = strlen($statement);
		for ($i = 0; $i < $s_len; $i++) {
			$char = $statement[$i];
			if ("'" === $char) {
				$next = ($i + 1 < $s_len) ? $statement[$i + 1] : '';
				if ($in_quote && "'" === $next) {
					$tuple .= "''";
					$i++;
					continue;
				}
				$in_quote = !$in_quote;
			}

			if ('(' === $char && !$in_quote) {
				$depth++;
				if (1 === $depth) {
					$tuple = '';
					continue;
				}
			}

			if (')' === $char && !$in_quote) {
				$depth--;
				if (0 === $depth) {
					$rows[] = stever_places_split_tuple($tuple);
					$tuple = '';
					continue;
				}
			}

			if ($depth > 0) {
				$tuple .= $char;
			}
		}
	}

	return $rows;
}

function stever_places_split_tuple($tuple) {
	$fields = array();
	$current = '';
	$in_quote = false;
	$len = strlen($tuple);

	for ($i = 0; $i < $len; $i++) {
		$char = $tuple[$i];

		if ("'" === $char) {
			$next = ($i + 1 < $len) ? $tuple[$i + 1] : '';
			if ($in_quote && "'" === $next) {
				$current .= "'";
				$i++;
				continue;
			}
			$in_quote = !$in_quote;
			continue;
		}

		if (',' === $char && !$in_quote) {
			$fields[] = trim($current);
			$current = '';
			continue;
		}

		$current .= $char;
	}

	$fields[] = trim($current);
	return $fields;
}

function stever_places_sql_value($value) {
	$value = trim((string) $value);
	if ('' === $value || 'NULL' === strtoupper($value)) {
		return '';
	}

	return $value;
}

function stever_places_fix_mojibake($text) {
	$text = (string) $text;
	if ('' === $text) {
		return '';
	}

	$marker_pattern = '/(?:Ãƒ.|Ãƒ(?=[\s,.;:!?)]|$)|Ã‚|Ã¢.|Ã°.|Å“|Å¾|ï¿½)/u';
	$original_markers = preg_match_all($marker_pattern, $text, $matches);
	if (!$original_markers) {
		return $text;
	}

	$direct_map = array(
		'ÃƒÂ¡' => 'Ã¡',
		'ÃƒÂ©' => 'Ã©',
		'ÃƒÂ­' => 'Ã­',
		'ÃƒÂ³' => 'Ã³',
		'ÃƒÂº' => 'Ãº',
		'ÃƒÂ' => 'Ã',
		'Ãƒâ€°' => 'Ã‰',
		'ÃƒÂ' => 'Ã',
		'Ãƒâ€œ' => 'Ã“',
		'ÃƒÅ¡' => 'Ãš',
		'ÃƒÂ±' => 'Ã±',
		'Ãƒâ€˜' => 'Ã‘',
		'ÃƒÂ¼' => 'Ã¼',
		'ÃƒÅ“' => 'Ãœ',
		'Ã¢â‚¬â„¢' => 'â€™',
		'Ã¢â‚¬Å“' => 'â€œ',
		'Ã¢â‚¬Â' => 'â€',
		'Ã¢â‚¬â€œ' => 'â€“',
		'Ã¢â‚¬â€' => 'â€”',
		'Ã‚ ' => ' ',
		'Ã‚' => '',
	);

	$direct_candidate = strtr($text, $direct_map);
	$direct_candidate = preg_replace('/Ãƒ(?=[\s,.;:!?)]|$)/u', 'Ã¡', $direct_candidate);
	if (!is_string($direct_candidate) || '' === $direct_candidate) {
		$direct_candidate = $text;
	}

	if (function_exists('mb_convert_encoding')) {
		$candidate = @mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
	} else {
		$candidate = @utf8_encode(@utf8_decode($text));
	}
	if (is_string($candidate) && '' !== $candidate) {
		$candidate = preg_replace('/Ãƒ(?=[\s,.;:!?)]|$)/u', 'Ã¡', $candidate);
	}

	if (!is_string($candidate) || '' === $candidate) {
		$candidate = $text;
	}

	$direct_markers = preg_match_all($marker_pattern, $direct_candidate, $matches_direct);
	$candidate_markers = preg_match_all($marker_pattern, $candidate, $matches_candidate);

	if ($direct_markers < $original_markers && $direct_markers <= $candidate_markers) {
		return $direct_candidate;
	}

	if ($candidate_markers < $original_markers) {
		return $candidate;
	}

	return $text;
}

function stever_places_normalize_text($text) {
	$text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	return stever_places_fix_mojibake($text);
}

function stever_places_clean_notes_text($notes) {
	$text = stever_places_normalize_text($notes);
	$text = preg_replace('/<\s*\/p\s*>\s*<\s*p[^>]*\s*>/i', "\n", $text);
	$text = preg_replace('/<\s*p[^>]*\s*>/i', '', $text);
	$text = preg_replace('/<\s*\/p\s*>/i', '', $text);
	$text = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $text);
	$text = preg_replace("/\r\n?/", "\n", $text);
	$text = preg_replace("/\n{3,}/", "\n\n", $text);

	return trim((string) $text);
}

function stever_places_decode_polyline_points($encoded) {
	$points = array();
	$index = 0;
	$lat = 0;
	$lng = 0;
	$len = strlen((string) $encoded);

	while ($index < $len) {
		$result = 0;
		$shift = 0;
		do {
			if ($index >= $len) {
				return $points;
			}
			$byte = ord($encoded[$index++]) - 63;
			$result |= ($byte & 0x1f) << $shift;
			$shift += 5;
		} while ($byte >= 0x20);

		$dlat = ($result & 1) ? ~($result >> 1) : ($result >> 1);
		$lat += $dlat;

		$result = 0;
		$shift = 0;
		do {
			if ($index >= $len) {
				return $points;
			}
			$byte = ord($encoded[$index++]) - 63;
			$result |= ($byte & 0x1f) << $shift;
			$shift += 5;
		} while ($byte >= 0x20);

		$dlng = ($result & 1) ? ~($result >> 1) : ($result >> 1);
		$lng += $dlng;

		$points[] = array($lat / 1e5, $lng / 1e5);
	}

	return $points;
}

function stever_places_parse_polyline_points($raw) {
	$text = trim((string) $raw);
	if ('' === $text) {
		return array();
	}

	$parsed = json_decode($text, true);
	if (is_array($parsed)) {
		$points = array();
		foreach ($parsed as $point) {
			if (is_array($point) && isset($point['lat']) && isset($point['lng'])) {
				$lat = floatval($point['lat']);
				$lng = floatval($point['lng']);
				if (is_finite($lat) && is_finite($lng)) {
					$points[] = array($lat, $lng);
				}
			} elseif (is_array($point) && isset($point[0]) && isset($point[1])) {
				$lat = floatval($point[0]);
				$lng = floatval($point[1]);
				if (is_finite($lat) && is_finite($lng)) {
					$points[] = array($lat, $lng);
				}
			}
		}
		if (!empty($points)) {
			return $points;
		}
	}

	if (preg_match_all('/\(\s*([-+]?\d+(?:\.\d+)?)\s*,\s*([-+]?\d+(?:\.\d+)?)\s*\)/', $text, $matches, PREG_SET_ORDER)) {
		$points = array();
		foreach ($matches as $match) {
			$points[] = array(floatval($match[1]), floatval($match[2]));
		}
		if (!empty($points)) {
			return $points;
		}
	}

	if (strpos($text, '|') !== false) {
		$parts = explode('|', $text);
		$points = array();
		foreach ($parts as $part) {
			$pair = explode(',', trim($part));
			if (count($pair) >= 2) {
				$lat = floatval($pair[0]);
				$lng = floatval($pair[1]);
				if (is_finite($lat) && is_finite($lng)) {
					$points[] = array($lat, $lng);
				}
			}
		}
		if (!empty($points)) {
			return $points;
		}
	}

	return stever_places_decode_polyline_points($text);
}

/* ===== Polyline Helpers ===== */

function stever_places_polyline_midpoint($polyline_data) {
	$points = stever_places_parse_polyline_points($polyline_data);
	if (count($points) < 2) {
		return null;
	}

	$index = (int) floor((count($points) - 1) / 2);
	if (!isset($points[$index])) {
		return null;
	}

	return array(
		'lat' => $points[$index][0],
		'lng' => $points[$index][1],
	);
}

function stever_places_apply_polyline_anchor_point($post_id, $polyline_data) {
	$midpoint = stever_places_polyline_midpoint($polyline_data);
	if (!is_array($midpoint)) {
		return false;
	}

	$next_lat = number_format((float) $midpoint['lat'], 8, '.', '');
	$next_lng = number_format((float) $midpoint['lng'], 8, '.', '');
	$current_lat = (string) get_post_meta($post_id, 'stever_place_latitude', true);
	$current_lng = (string) get_post_meta($post_id, 'stever_place_longitude', true);

	if ($current_lat === $next_lat && $current_lng === $next_lng) {
		return false;
	}

	update_post_meta($post_id, 'stever_place_latitude', $next_lat);
	update_post_meta($post_id, 'stever_place_longitude', $next_lng);

	return true;
}

/* ===== Place Info Taxonomy Helpers ===== */

function stever_places_resolve_place_info_child_slug($map_slug, $legacy_type) {
	$map_slug = stever_places_normalize_map_slug($map_slug);
	$legacy_slug = sanitize_title((string) $legacy_type);

	if ('been' === $map_slug) {
		if (in_array($legacy_slug, array('lived'), true)) {
			return 'been-lived';
		}
		if (in_array($legacy_slug, array('layover', 'pass-through', 'layover-pass-through', 'layover-pass-thru'), true)) {
			return 'been-layover-pass-through';
		}
		return 'been-general';
	}

	if ('want-to-go' === $map_slug) {
		if (in_array($legacy_slug, array('outdoors', 'outdoor'), true)) {
			return 'want-outdoors';
		}
		if (in_array($legacy_slug, array('indoors', 'indoor'), true)) {
			return 'want-indoors';
		}
		return 'want-general';
	}

	return '';
}

function stever_places_detect_parent_map_slug($terms) {
	if (is_wp_error($terms) || empty($terms)) {
		return '';
	}

	foreach ($terms as $term) {
		if ('been' === $term->slug || 'want-to-go' === $term->slug) {
			return $term->slug;
		}
	}

	foreach ($terms as $term) {
		if (intval($term->parent) > 0) {
			$parent = get_term(intval($term->parent), 'stever_place_map');
			if ($parent && !is_wp_error($parent) && ('been' === $parent->slug || 'want-to-go' === $parent->slug)) {
				return $parent->slug;
			}
		}
	}

	return '';
}

function stever_places_get_place_info_child_slug($terms, $map_slug) {
	if (is_wp_error($terms) || empty($terms)) {
		return '';
	}

	foreach ($terms as $term) {
		if (intval($term->parent) > 0) {
			$parent = get_term(intval($term->parent), 'stever_place_map');
			if ($parent && !is_wp_error($parent) && $parent->slug === $map_slug) {
				return $term->slug;
			}
		}
	}

	return '';
}

function stever_places_get_place_type_label($terms, $map_slug) {
	if (is_wp_error($terms) || empty($terms)) {
		return '';
	}

	foreach ($terms as $term) {
		if (intval($term->parent) > 0) {
			$parent = get_term(intval($term->parent), 'stever_place_map');
			if ($parent && !is_wp_error($parent) && $parent->slug === $map_slug) {
				return $term->name;
			}
		}
	}

	return '';
}

function stever_places_import_rows($rows, $map_slug) {
	$imported = 0;

	foreach ($rows as $row) {
		if (count($row) < 9) {
			continue;
		}

		$name = stever_places_normalize_text(stever_places_sql_value($row[2]));
		$category = stever_places_sql_value($row[3]);
		$source_type = stever_places_sql_value($row[4]);
		$latitude = stever_places_sql_value($row[5]);
		$longitude = stever_places_sql_value($row[6]);
		$polyline_data = stever_places_sql_value($row[7]);
		$notes = stever_places_sql_value($row[8]);

		if ('' === $name) {
			continue;
		}

		$post_id = wp_insert_post(array(
			'post_type' => 'stever_place',
			'post_status' => 'publish',
			'post_title' => wp_strip_all_tags($name),
		));

		if (is_wp_error($post_id) || !$post_id) {
			continue;
		}

		$child_slug = stever_places_resolve_place_info_child_slug($map_slug, $category);
		$term_ids = stever_places_get_place_info_assignment_term_ids($map_slug, $child_slug);
		wp_set_object_terms($post_id, $term_ids, 'stever_place_map', false);
		wp_set_object_terms($post_id, array(), 'stever_place_type', false);

		// Source table/id intentionally not stored.
		$normalized_source_type = ('Polylines' === $source_type) ? 'Polyline' : $source_type;
		update_post_meta($post_id, 'stever_place_source_type', in_array($normalized_source_type, array('Point', 'Polyline'), true) ? $normalized_source_type : 'Point');
		update_post_meta($post_id, 'stever_place_latitude', $latitude);
		update_post_meta($post_id, 'stever_place_longitude', $longitude);
		update_post_meta($post_id, 'stever_place_polyline_data', $polyline_data);
		update_post_meta($post_id, 'stever_place_notes', stever_places_clean_notes_text($notes));
		if ('Polylines' === $source_type || 'Polyline' === $source_type || '' !== trim((string) $polyline_data)) {
			stever_places_apply_polyline_anchor_point($post_id, $polyline_data);
		}

		$imported++;
	}

	return $imported;
}

function stever_places_normalize_map_slug($map_slug) {
	$map_slug = sanitize_title((string) $map_slug);

	if ('been' === $map_slug || 'want-to-go' === $map_slug) {
		return $map_slug;
	}

	return '';
}

function stever_places_import_polyline_rows($rows) {
	$imported = 0;

	foreach ($rows as $row) {
		if (count($row) < 7) {
			continue;
		}

		$map_slug = stever_places_normalize_map_slug(stever_places_sql_value($row[2]));
		$name = stever_places_normalize_text(stever_places_sql_value($row[3]));
		$source_type = stever_places_sql_value($row[4]);
		$polyline_data = stever_places_sql_value($row[5]);
		$notes = stever_places_sql_value($row[6]);

		if ('' === $map_slug || '' === $name || '' === $polyline_data) {
			continue;
		}

		$post_id = wp_insert_post(array(
			'post_type' => 'stever_place',
			'post_status' => 'publish',
			'post_title' => wp_strip_all_tags($name),
		));

		if (is_wp_error($post_id) || !$post_id) {
			continue;
		}

		$child_slug = stever_places_resolve_place_info_child_slug($map_slug, '');
		$term_ids = stever_places_get_place_info_assignment_term_ids($map_slug, $child_slug);
		wp_set_object_terms($post_id, $term_ids, 'stever_place_map', false);
		update_post_meta($post_id, 'stever_place_source_type', 'Polyline');
		update_post_meta($post_id, 'stever_place_polyline_data', $polyline_data);
		update_post_meta($post_id, 'stever_place_notes', stever_places_clean_notes_text($notes));
		stever_places_apply_polyline_anchor_point($post_id, $polyline_data);

		$imported++;
	}

	return $imported;
}

function stever_places_detect_import_type($filename, $sql, $rows) {
	$filename = strtolower((string) $filename);
	$sql = strtolower((string) $sql);

	if (false !== strpos($filename, 'polylines.sql') || false !== strpos($sql, 'create table `polylines`') || false !== strpos($sql, 'insert into `polylines`')) {
		return 'polylines';
	}

	if (false !== strpos($filename, 'places_ive_been.sql') || false !== strpos($sql, 'create table `places_ive_been`') || false !== strpos($sql, 'insert into `places_ive_been`')) {
		return 'been';
	}

	if (false !== strpos($filename, 'places_id_like_to_go.sql') || false !== strpos($sql, 'create table `places_id_like_to_go`') || false !== strpos($sql, 'insert into `places_id_like_to_go`')) {
		return 'want-to-go';
	}

	if (!empty($rows) && isset($rows[0]) && is_array($rows[0])) {
		$first_row = $rows[0];
		$first_row_source = stever_places_sql_value($first_row[4]);
		if (count($first_row) >= 7 && ('Polylines' === $first_row_source || 'Polyline' === $first_row_source) && ('been' === stever_places_sql_value($first_row[2]) || 'want-to-go' === stever_places_sql_value($first_row[2]))) {
			return 'polylines';
		}
	}

	return '';
}

function stever_places_handle_save_settings() {
	if (!current_user_can('manage_options')) {
		wp_die('You are not allowed to do this.');
	}

	check_admin_referer('stever_places_settings_nonce', 'stever_places_settings_nonce_field');

	$settings = array(
		'tile_url' => isset($_POST['tile_url']) && '' !== trim((string) wp_unslash($_POST['tile_url'])) ? sanitize_text_field(wp_unslash($_POST['tile_url'])) : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
		'tile_attribution' => isset($_POST['tile_attribution']) && '' !== trim((string) wp_unslash($_POST['tile_attribution'])) ? wp_kses_post(wp_unslash($_POST['tile_attribution'])) : '&copy; OpenStreetMap contributors &copy; CARTO',
		'geocode_url' => isset($_POST['geocode_url']) ? esc_url_raw(wp_unslash($_POST['geocode_url'])) : '',
		'geocode_email' => isset($_POST['geocode_email']) ? sanitize_email(wp_unslash($_POST['geocode_email'])) : '',
		'api_key' => isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '',
		'default_zoom' => isset($_POST['default_zoom']) ? intval($_POST['default_zoom']) : 2,
	);

	$settings['default_zoom'] = max(1, min(18, $settings['default_zoom']));
	update_option('stever_places_settings', $settings, false);

	stever_places_redirect_tools_page(array('stever_places_status' => 'success', 'message' => 'Settings saved.'));
}
add_action('admin_post_stever_places_save_settings', 'stever_places_handle_save_settings');

