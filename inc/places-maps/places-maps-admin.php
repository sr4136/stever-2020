<?php
/**
 * Places maps module: admin metabox, validation, list table, and settings UI.
 */

function stever_places_add_meta_boxes() {
	add_meta_box(
		'stever_place_location_box',
		'Place Location',
		'stever_places_render_location_metabox',
		'stever_place',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', 'stever_places_add_meta_boxes');

function stever_places_render_location_metabox($post) {
	$lat = get_post_meta($post->ID, 'stever_place_latitude', true);
	$lng = get_post_meta($post->ID, 'stever_place_longitude', true);
	$source_type = get_post_meta($post->ID, 'stever_place_source_type', true);
	$source_type = ('Polylines' === $source_type) ? 'Polyline' : $source_type;
	$polyline_data = get_post_meta($post->ID, 'stever_place_polyline_data', true);
	$notes = get_post_meta($post->ID, 'stever_place_notes', true);

	wp_nonce_field('stever_places_save_metabox', 'stever_places_metabox_nonce');
	?>
	<div class="stever-place-admin-section stever-place-admin-search-wrapper">
	
		<label for="stever_place_geocode_query"><strong>Search by place name, address, or "lat,lng"</strong></label><br />
		
		<div class="stever-place-admin-search">
			<input type="text" id="stever_place_geocode_query" class="widefat" placeholder="Example: Boston, MA or 42.3601,-71.0589" />
			<button type="button" class="button stever-place-admin-button" id="stever_place_geocode_search">Find</button>
			<span id="stever_place_geocode_status" class="stever-place-admin-status"></span>
		</div>
	</div>

	<div id="stever-place-admin-map" class="stever-place-admin-section stever-place-admin-map" data-lat="<?php echo esc_attr($lat); ?>" data-lng="<?php echo esc_attr($lng); ?>"></div>

	<div class="stever-place-admin-section stever-place-admin-grid">
		<p>
			<label for="stever_place_latitude"><strong>Latitude</strong></label><br />
			<input type="text" id="stever_place_latitude" name="stever_place_latitude" value="<?php echo esc_attr($lat); ?>" class="regular-text" />
		</p>
		<p>
			<label for="stever_place_longitude"><strong>Longitude</strong></label><br />
			<input type="text" id="stever_place_longitude" name="stever_place_longitude" value="<?php echo esc_attr($lng); ?>" class="regular-text" />
		</p>
	</div>

	<div class="stever-place-admin-section stever-place-admin-notes">
		<label for="stever_place_notes"><strong>Notes</strong></label><br />
		<textarea id="stever_place_notes" name="stever_place_notes" class="large-text" rows="4"><?php echo esc_textarea($notes); ?></textarea>
	</div>

	<div class="stever-place-admin-section stever-place-admin-grid">
		<p>
			<label for="stever_place_source_type"><strong>Source Type</strong></label><br />
			<select id="stever_place_source_type" name="stever_place_source_type">
				<option value="Point" <?php selected($source_type, 'Point'); ?>>Point</option>
				<option value="Polyline" <?php selected($source_type, 'Polyline'); ?>>Polyline</option>
			</select>
		</p>
	</div>

	<div class="stever-place-admin-section stever-place-admin-polyline">
		<label for="stever_place_polyline_data"><strong>Polyline Data (raw)</strong> <a href="#" class="stever-place-gpx-import" id="stever_place_gpx_import">Import .gpx data from clipboard</a></label><br />
		<span id="stever_place_gpx_status" class="stever-place-admin-status"></span>
		<textarea id="stever_place_polyline_data" name="stever_place_polyline_data" class="large-text" rows="3"><?php echo esc_textarea($polyline_data); ?></textarea>
	</div>


	<?php
}

function stever_places_save_meta($post_id) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!isset($_POST['stever_places_metabox_nonce']) || !wp_verify_nonce($_POST['stever_places_metabox_nonce'], 'stever_places_save_metabox')) {
		return;
	}

	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	if ('stever_place' !== get_post_type($post_id)) {
		return;
	}

	$lat = isset($_POST['stever_place_latitude']) ? sanitize_text_field(wp_unslash($_POST['stever_place_latitude'])) : '';
	$lng = isset($_POST['stever_place_longitude']) ? sanitize_text_field(wp_unslash($_POST['stever_place_longitude'])) : '';
	$source_type = isset($_POST['stever_place_source_type']) ? sanitize_text_field(wp_unslash($_POST['stever_place_source_type'])) : 'Point';
	$polyline_data = isset($_POST['stever_place_polyline_data']) ? wp_kses_post(wp_unslash($_POST['stever_place_polyline_data'])) : '';
	$notes = isset($_POST['stever_place_notes']) ? wp_kses_post(wp_unslash($_POST['stever_place_notes'])) : '';

	update_post_meta($post_id, 'stever_place_latitude', $lat);
	update_post_meta($post_id, 'stever_place_longitude', $lng);
	$normalized_source_type = ('Polylines' === $source_type) ? 'Polyline' : $source_type;
	update_post_meta($post_id, 'stever_place_source_type', in_array($normalized_source_type, array('Point', 'Polyline'), true) ? $normalized_source_type : 'Point');
	update_post_meta($post_id, 'stever_place_polyline_data', $polyline_data);
	update_post_meta($post_id, 'stever_place_notes', $notes);
}
add_action('save_post', 'stever_places_save_meta');

function stever_places_term_checklist_args($args, $post_id) {
	if (empty($args['taxonomy']) || 'stever_place_map' !== $args['taxonomy']) {
		return $args;
	}

	// Keep checked Place Info terms in their natural hierarchy instead of moving them to the top.
	$args['checked_ontop'] = false;

	return $args;
}
add_filter('wp_terms_checklist_args', 'stever_places_term_checklist_args', 10, 2);

function stever_places_has_submitted_place_info_term() {
	if (!isset($_POST['tax_input']) || !is_array($_POST['tax_input'])) {
		return false;
	}

	if (!isset($_POST['tax_input']['stever_place_map'])) {
		return false;
	}

	$raw_terms = wp_unslash($_POST['tax_input']['stever_place_map']);
	$terms = is_array($raw_terms) ? $raw_terms : array($raw_terms);

	foreach ($terms as $term_value) {
		if ('' !== trim((string) $term_value)) {
			return true;
		}
	}

	return false;
}

function stever_places_validate_map_taxonomy($data, $postarr) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $data;
	}

	if (!isset($postarr['ID'])) {
		return $data;
	}

	if ('stever_place' !== $data['post_type']) {
		return $data;
	}

	if ('publish' !== $data['post_status']) {
		return $data;
	}

	if (!current_user_can('edit_post', $postarr['ID'])) {
		return $data;
	}

	// During first publish, selected terms may not be written yet. Honor submitted taxonomy input.
	$has_submitted_term = stever_places_has_submitted_place_info_term();
	$map_terms = wp_get_object_terms($postarr['ID'], 'stever_place_map', array('fields' => 'ids'));
	$has_saved_term = !is_wp_error($map_terms) && !empty($map_terms);

	if (!$has_saved_term && !$has_submitted_term) {
		$data['post_status'] = 'draft';
		set_transient('stever_places_blocked_publish_' . $postarr['ID'], true, 10);
		return $data;
	}

	// Clear stale warning state when publish is allowed.
	delete_transient('stever_places_blocked_publish_' . $postarr['ID']);

	return $data;
}
add_filter('wp_insert_post_data', 'stever_places_validate_map_taxonomy', 10, 2);

function stever_places_show_blocked_publish_notice() {
	global $post;
	if (!$post || 'stever_place' !== $post->post_type) {
		return;
	}

	if (get_transient('stever_places_blocked_publish_' . $post->ID)) {
		delete_transient('stever_places_blocked_publish_' . $post->ID);
		echo '<div class="notice notice-error is-dismissible"><p><strong>Post saved as Draft until Place Info is chosen.</strong></p></div>';
	}
}
add_action('admin_notices', 'stever_places_show_blocked_publish_notice');

function stever_places_admin_columns($columns) {
	$updated = array();

	foreach ($columns as $key => $label) {
		$updated[$key] = $label;
		if ('taxonomy-stever_place_map' === $key) {
			$updated['stever_place_polyline'] = 'Polyline';
		}
	}

	if (!isset($updated['stever_place_polyline'])) {
		$updated['stever_place_polyline'] = 'Polyline';
	}

	return $updated;
}
add_filter('manage_stever_place_posts_columns', 'stever_places_admin_columns');

function stever_places_admin_column_content($column, $post_id) {
	if ('stever_place_polyline' !== $column) {
		return;
	}

	$polyline_data = get_post_meta($post_id, 'stever_place_polyline_data', true);
	if ('' !== trim((string) $polyline_data)) {
		echo 'Polyline';
	}
}
add_action('manage_stever_place_posts_custom_column', 'stever_places_admin_column_content', 10, 2);

function stever_places_sortable_columns($columns) {
	$columns['stever_place_polyline'] = 'stever_place_polyline';
	return $columns;
}
add_filter('manage_edit-stever_place_sortable_columns', 'stever_places_sortable_columns');

function stever_places_sort_admin_query($query) {
	if (!is_admin() || !$query->is_main_query()) {
		return;
	}

	if ('stever_place' !== $query->get('post_type')) {
		return;
	}

	if ('stever_place_polyline' !== $query->get('orderby')) {
		return;
	}

	$query->set('meta_key', 'stever_place_polyline_data');
	$query->set('orderby', 'meta_value');
	$query->set('meta_type', 'CHAR');
}
add_action('pre_get_posts', 'stever_places_sort_admin_query');

function stever_places_admin_assets($hook) {
	$screen = get_current_screen();
	if (!$screen) {
		return;
	}

	$is_place_editor = in_array($hook, array('post.php', 'post-new.php'), true) && 'stever_place' === $screen->post_type;
	if (!$is_place_editor) {
		return;
	}

	$settings = stever_places_get_settings();

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
		'stever-places-admin-js',
		get_stylesheet_directory_uri() . '/js/places-admin.js',
		array('stever-leaflet'),
		filemtime(get_stylesheet_directory() . '/js/places-admin.js'),
		true
	);

	wp_localize_script('stever-places-admin-js', 'steverPlacesAdmin', array(
		'tileUrl' => $settings['tile_url'],
		'tileAttribution' => $settings['tile_attribution'],
		'geocodeUrl' => $settings['geocode_url'],
		'geocodeEmail' => $settings['geocode_email'],
		'apiKey' => $settings['api_key'],
		'defaultZoom' => $settings['default_zoom'],
	));
}
add_action('admin_enqueue_scripts', 'stever_places_admin_assets');

function stever_places_register_admin_page() {
	add_submenu_page(
		'edit.php?post_type=stever_place',
		'Places Settings',
		'Settings',
		'manage_options',
		'stever-place-tools',
		'stever_places_render_tools_page'
	);
}
add_action('admin_menu', 'stever_places_register_admin_page');

function stever_places_render_tools_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$settings = stever_places_get_settings();
	$status = isset($_GET['stever_places_status']) ? sanitize_text_field(wp_unslash($_GET['stever_places_status'])) : '';
	$message = isset($_GET['message']) ? sanitize_text_field(wp_unslash($_GET['message'])) : '';
	?>
	<div class="wrap">
		<h1>Places Settings</h1>

		<?php if ($status) : ?>
			<div class="notice notice-<?php echo 'error' === $status ? 'error' : 'success'; ?> is-dismissible"><p><?php echo esc_html($message); ?></p></div>
		<?php endif; ?>

		<h2>OpenStreetMap Config</h2>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<?php wp_nonce_field('stever_places_settings_nonce', 'stever_places_settings_nonce_field'); ?>
			<input type="hidden" name="action" value="stever_places_save_settings" />
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="tile_url">Tile URL Template</label></th>
					<td><input type="text" id="tile_url" name="tile_url" class="regular-text" value="<?php echo esc_attr($settings['tile_url']); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="tile_attribution">Tile Attribution</label></th>
					<td><input type="text" id="tile_attribution" name="tile_attribution" class="regular-text" value="<?php echo esc_attr($settings['tile_attribution']); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="geocode_url">Geocode Endpoint</label></th>
					<td><input type="url" id="geocode_url" name="geocode_url" class="regular-text" value="<?php echo esc_attr($settings['geocode_url']); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="geocode_email">Geocode Email (optional)</label></th>
					<td><input type="email" id="geocode_email" name="geocode_email" class="regular-text" value="<?php echo esc_attr($settings['geocode_email']); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="api_key">API Key (optional)</label></th>
					<td><input type="text" id="api_key" name="api_key" class="regular-text" value="<?php echo esc_attr($settings['api_key']); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="default_zoom">Default Zoom</label></th>
					<td><input type="number" id="default_zoom" name="default_zoom" min="1" max="18" value="<?php echo esc_attr($settings['default_zoom']); ?>" /></td>
				</tr>
			</table>
			<p><button type="submit" class="button button-primary">Save Settings</button></p>
		</form>

		<?php if (function_exists('stever_places_render_import_tools_panel')) : ?>
			<?php stever_places_render_import_tools_panel(); ?>
		<?php endif; ?>
	</div>
	<?php
}

function stever_places_redirect_tools_page($args = array()) {
	$url = add_query_arg(
		wp_parse_args($args, array('post_type' => 'stever_place', 'page' => 'stever-place-tools')),
		admin_url('edit.php')
	);
	wp_safe_redirect($url);
	exit;
}

