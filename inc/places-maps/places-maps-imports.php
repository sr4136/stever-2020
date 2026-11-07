<?php
/**
 * Optional Places imports module.
 *
 * Enable from places-maps.php when import/clear tooling is needed again.
 */

if (!defined('ABSPATH')) {
	exit;
}

function stever_places_render_import_tools_panel() {
	?>
	<hr />
	<h2>Import Tools</h2>
	<p>Use this panel only when you need to re-import exported SQL data.</p>
	<p>Import now includes cleanup automatically (title/notes normalization, taxonomy re-assignment, and polyline anchor updates).</p>
	<ul>
		<li><strong>places_ive_been.sql</strong> -> Places I've Been</li>
		<li><strong>places_id_like_to_go.sql</strong> -> Places I'd Like to Go</li>
		<li><strong>polylines.sql</strong> -> Polyline-only import for both maps</li>
	</ul>

	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
		<?php wp_nonce_field('stever_places_import_nonce', 'stever_places_import_nonce_field'); ?>
		<input type="hidden" name="action" value="stever_places_import_sql" />
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="stever_places_sql_file">SQL File</label></th>
				<td><input type="file" id="stever_places_sql_file" name="stever_places_sql_file" accept=".sql,text/plain" /></td>
			</tr>
		</table>
		<p><button type="submit" class="button button-primary">Import SQL File</button></p>
	</form>

	<h3>Danger Zone</h3>
	<p>Delete all Place posts created for this feature.</p>
	<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
		<?php wp_nonce_field('stever_places_clear_nonce', 'stever_places_clear_nonce_field'); ?>
		<input type="hidden" name="action" value="stever_places_clear_all" />
		<p><button type="submit" class="button">Clear All Places</button></p>
	</form>
	<?php
}

function stever_places_run_import_cleanup() {
	stever_places_ensure_place_info_terms();

	$post_ids = get_posts(array(
		'post_type' => 'stever_place',
		'post_status' => 'any',
		'fields' => 'ids',
		'posts_per_page' => -1,
	));

	$summary = array(
		'notes_updated' => 0,
		'titles_updated' => 0,
		'terms_updated' => 0,
		'general_assigned' => 0,
		'legacy_type_cleared' => 0,
		'polyline_anchor_updated' => 0,
		'missing_parent' => 0,
	);

	foreach ($post_ids as $post_id) {
		$current_title = get_the_title($post_id);
		$clean_title = trim(wp_strip_all_tags(stever_places_normalize_text($current_title)));
		if ('' !== $clean_title && $clean_title !== $current_title) {
			wp_update_post(array(
				'ID' => $post_id,
				'post_title' => $clean_title,
			));
			$summary['titles_updated']++;
		}

		$current_notes = get_post_meta($post_id, 'stever_place_notes', true);
		$cleaned_notes = stever_places_clean_notes_text($current_notes);
		if ((string) $current_notes !== (string) $cleaned_notes) {
			update_post_meta($post_id, 'stever_place_notes', $cleaned_notes);
			$summary['notes_updated']++;
		}

		$polyline_data = get_post_meta($post_id, 'stever_place_polyline_data', true);
		if ('' !== trim((string) $polyline_data) && stever_places_apply_polyline_anchor_point($post_id, $polyline_data)) {
			$summary['polyline_anchor_updated']++;
		}

		$map_terms = wp_get_object_terms($post_id, 'stever_place_map');
		$map_slug = stever_places_detect_parent_map_slug($map_terms);
		if ('' === $map_slug) {
			$summary['missing_parent']++;
			continue;
		}

		$current_child_slug = stever_places_get_place_info_child_slug($map_terms, $map_slug);
		$legacy_type_terms = wp_get_object_terms($post_id, 'stever_place_type', array('fields' => 'slugs'));
		$legacy_type_slug = (!is_wp_error($legacy_type_terms) && !empty($legacy_type_terms)) ? $legacy_type_terms[0] : '';
		$child_slug = '' !== $current_child_slug ? $current_child_slug : stever_places_resolve_place_info_child_slug($map_slug, $legacy_type_slug);

		if ('' !== $child_slug) {
			$term_ids = stever_places_get_place_info_assignment_term_ids($map_slug, $child_slug);
			wp_set_object_terms($post_id, $term_ids, 'stever_place_map', false);
			$summary['terms_updated']++;
			if ('' === $current_child_slug && false !== strpos($child_slug, '-general')) {
				$summary['general_assigned']++;
			}
		}

		if (!is_wp_error($legacy_type_terms) && !empty($legacy_type_terms)) {
			wp_set_object_terms($post_id, array(), 'stever_place_type', false);
			$summary['legacy_type_cleared']++;
		}
	}

	return $summary;
}

function stever_places_format_cleanup_message($summary) {
	return sprintf(
		'Cleanup after import complete. Titles fixed: %1$d. Notes updated: %2$d. Place Info terms reassigned: %3$d. General assigned: %4$d. Polyline anchor points updated: %5$d. Legacy Place Types cleared: %6$d. Missing parent map: %7$d.',
		intval($summary['titles_updated']),
		intval($summary['notes_updated']),
		intval($summary['terms_updated']),
		intval($summary['general_assigned']),
		intval($summary['polyline_anchor_updated']),
		intval($summary['legacy_type_cleared']),
		intval($summary['missing_parent'])
	);
}

function stever_places_handle_import() {
	if (!current_user_can('manage_options')) {
		wp_die('You are not allowed to do this.');
	}

	check_admin_referer('stever_places_import_nonce', 'stever_places_import_nonce_field');

	$has_file = isset($_FILES['stever_places_sql_file']) && !empty($_FILES['stever_places_sql_file']['tmp_name']);
	if (!$has_file) {
		stever_places_redirect_tools_page(array('stever_places_status' => 'error', 'message' => 'Please select a SQL file to import.'));
	}

	$sql = file_get_contents($_FILES['stever_places_sql_file']['tmp_name']);
	$rows = stever_places_parse_insert_values((string) $sql);
	$import_type = stever_places_detect_import_type($_FILES['stever_places_sql_file']['name'], (string) $sql, $rows);

	if ('' === $import_type) {
		stever_places_redirect_tools_page(array('stever_places_status' => 'error', 'message' => 'Could not determine how to import that SQL file. Use places_ive_been.sql, places_id_like_to_go.sql, or polylines.sql.'));
	}

	$total_imported = ('polylines' === $import_type)
		? stever_places_import_polyline_rows($rows)
		: stever_places_import_rows($rows, $import_type);

	$cleanup_summary = stever_places_run_import_cleanup();
	$message = 'Imported ' . intval($total_imported) . ' places. ' . stever_places_format_cleanup_message($cleanup_summary);

	stever_places_redirect_tools_page(array('stever_places_status' => 'success', 'message' => $message));
}
add_action('admin_post_stever_places_import_sql', 'stever_places_handle_import');

function stever_places_handle_clear() {
	if (!current_user_can('manage_options')) {
		wp_die('You are not allowed to do this.');
	}

	check_admin_referer('stever_places_clear_nonce', 'stever_places_clear_nonce_field');
	$count = stever_places_clear_posts();
	stever_places_redirect_tools_page(array('stever_places_status' => 'success', 'message' => 'Deleted ' . intval($count) . ' places.'));
}
add_action('admin_post_stever_places_clear_all', 'stever_places_handle_clear');
