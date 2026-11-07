<?php
/**
 * Places maps module: frontend point collection and shortcode rendering.
 */

function stever_places_collect_points($map_slug) {
	$tax_query = array(
		array(
			'taxonomy' => 'stever_place_map',
			'field' => 'slug',
			'terms' => array($map_slug),
		),
	);

	$query = new WP_Query(array(
		'post_type' => 'stever_place',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'tax_query' => $tax_query,
		'orderby' => 'title',
		'order' => 'ASC',
	));

	$points = array();
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			$post_id = get_the_ID();
			$lat = get_post_meta($post_id, 'stever_place_latitude', true);
			$lng = get_post_meta($post_id, 'stever_place_longitude', true);
			$notes = get_post_meta($post_id, 'stever_place_notes', true);
			$source_type = get_post_meta($post_id, 'stever_place_source_type', true);
			$polyline_data = get_post_meta($post_id, 'stever_place_polyline_data', true);
			$is_polyline = ('Polylines' === $source_type || 'Polyline' === $source_type) || '' !== trim((string) $polyline_data);

			if (('' === $lat || '' === $lng) && !$is_polyline) {
				continue;
			}

			$map_terms = wp_get_object_terms($post_id, 'stever_place_map');
			$place_type = stever_places_get_place_type_label($map_terms, $map_slug);
			$edit_link = get_edit_post_link($post_id, '');

			$points[] = array(
				'title' => get_the_title(),
				'latitude' => floatval($lat),
				'longitude' => floatval($lng),
				'content' => wp_kses_post($notes),
				'dataType' => $is_polyline ? 'Polyline' : 'Point',
				'polylineData' => $polyline_data ? $polyline_data : '',
				'placeType' => $place_type,
				'editUrl' => $edit_link ? esc_url_raw($edit_link) : '',
			);
		}
		wp_reset_postdata();
	}

	return $points;
}

/**
 * Frontend shortcode: [stever_places_map map="been|want-to-go"]
 * Renders an interactive Leaflet map with toolbar and location search.
 */
function stever_places_shortcode($atts) {
	$atts = shortcode_atts(array(
		'map' => 'been',
	), $atts, 'stever_places_map');

	$settings = stever_places_get_settings();
	$map_slug = sanitize_title($atts['map']);
	$zoom = intval($settings['default_zoom']);
	$points = stever_places_collect_points($map_slug);

	wp_enqueue_style(
		'stever-leaflet',
		'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
		array(),
		'1.9.4'
	);
	wp_enqueue_script(
		'stever-leaflet',
		'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
		array(),
		'1.9.4',
		true
	);

	wp_enqueue_script(
		'stever-places-front-js',
		get_stylesheet_directory_uri() . '/js/places-maps.js',
		array('stever-leaflet'),
		filemtime(get_stylesheet_directory() . '/js/places-maps.js'),
		true
	);

	$uid = 'stever-places-map-' . wp_rand(1000, 9999999);
	$payload = wp_json_encode(array(
		'features' => $points,
		'tileUrl' => $settings['tile_url'],
		'tileAttribution' => $settings['tile_attribution'],
		'geocodeUrl' => $settings['geocode_url'],
		'geocodeEmail' => $settings['geocode_email'],
		'apiKey' => $settings['api_key'],
		'zoom' => $zoom,
	));

	$info_text = count($points) . ' place' . (count($points) !== 1 ? 's' : '');

	ob_start();
	?>
	<div class="stever-places-map-wrap">
		<div id="<?php echo esc_attr($uid); ?>" class="stever-places-map" data-places-config="<?php echo esc_attr($payload); ?>"></div>
		<p class="stever-places-map-info"><?php echo esc_html($info_text); ?></p>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode('stever_places_map', 'stever_places_shortcode'); /* Register frontend shortcode handler */
