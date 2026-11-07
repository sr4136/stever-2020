<?php
/**
 * Places maps module: settings, CPT/taxonomy registration, and term hierarchy.
 */

function stever_places_default_settings() {
	return array(
		'tile_url' => 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png',
		'tile_attribution' => '&copy; OpenStreetMap contributors &copy; CARTO',
		'geocode_url' => 'https://nominatim.openstreetmap.org/search',
		'geocode_email' => '',
		'api_key' => '',
		'default_zoom' => 2,
	);
}

function stever_places_get_settings() {
	$defaults = stever_places_default_settings();
	$saved = get_option('stever_places_settings', array());

	if (!is_array($saved)) {
		$saved = array();
	}

	$settings = wp_parse_args($saved, $defaults);

	if (empty($settings['tile_url']) || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png' === $settings['tile_url']) {
		$settings['tile_url'] = $defaults['tile_url'];
	}

	if (empty($settings['tile_attribution']) || '&copy; OpenStreetMap contributors' === $settings['tile_attribution']) {
		$settings['tile_attribution'] = $defaults['tile_attribution'];
	}

	$settings['default_zoom'] = max(1, min(18, intval($settings['default_zoom'])));

	return $settings;
}

/* ===== CPT & Taxonomy Registration ===== */

function stever_places_register_cpt_and_taxonomies() {
	$labels = array(
		'name' => 'Places',
		'menu_name' => 'Places',
		'add_new_item' => 'Add New Place',
		'edit_item' => 'Edit Place',
		'new_item' => 'New Place',
		'view_item' => 'View Place',
		'search_items' => 'Search Places',
	);

	$args = array(
		'label' => 'Place',
		'labels' => $labels,
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'show_in_rest' => false,
		'has_archive' => false,
		'publicly_queryable' => false,
		'exclude_from_search' => true,
		'supports' => array('title'),
		'menu_icon' => 'dashicons-location-alt',
		'rewrite' => false,
		'capability_type' => 'post',
	);

	register_post_type('stever_place', $args);


	register_taxonomy(
		'stever_place_map',
		array('stever_place'),
		array(
			'labels' => array(
				'name' => 'Place Info',
				'singular_name' => 'Place Info',
			),
			'hierarchical' => true,
			'public' => false,
			'show_ui' => true,
			'show_admin_column' => true,
			'show_in_rest' => false,
		)
	);

	register_taxonomy(
		'stever_place_type',
		array('stever_place'),
		array(
			'labels' => array(
				'name' => 'Legacy Place Types',
				'singular_name' => 'Legacy Place Type',
			),
			'hierarchical' => true,
			'public' => false,
			'show_ui' => false,
			'show_admin_column' => false,
			'show_in_rest' => false,
		)
	);
}
add_action('init', 'stever_places_register_cpt_and_taxonomies', 0);

function stever_places_disable_block_editor($enabled, $post_type) {
	if ('stever_place' === $post_type) {
		return false;
	}

	return $enabled;
}
add_filter('use_block_editor_for_post_type', 'stever_places_disable_block_editor', 10, 2);

function stever_places_place_info_hierarchy() {
	return array(
		'been' => array(
			'name' => 'Places I\'ve Been',
			'children' => array(
				'been-general' => 'General',
				'been-lived' => 'Lived',
				'been-layover-pass-through' => 'Layover / Pass Through',
			),
		),
		'want-to-go' => array(
			'name' => 'Places I\'d Like to Go',
			'children' => array(
				'want-general' => 'General',
				'want-outdoors' => 'Outdoors',
				'want-indoors' => 'Indoors',
			),
		),
	);
}

function stever_places_find_term_for_hierarchy($taxonomy, $slug, $name) {
	$term = get_term_by('slug', $slug, $taxonomy);
	if ($term && !is_wp_error($term)) {
		return $term;
	}

	if ('General' === $name) {
		return false;
	}

	$term = get_term_by('name', $name, $taxonomy);
	if ($term && !is_wp_error($term)) {
		return $term;
	}

	return false;
}

function stever_places_ensure_place_info_terms() {
	$term_ids = array();
	$hierarchy = stever_places_place_info_hierarchy();

	foreach ($hierarchy as $parent_slug => $parent_data) {
		$parent = stever_places_find_term_for_hierarchy('stever_place_map', $parent_slug, $parent_data['name']);
		if (!$parent || is_wp_error($parent)) {
			$created = wp_insert_term($parent_data['name'], 'stever_place_map', array('slug' => $parent_slug));
			if (is_wp_error($created) || !isset($created['term_id'])) {
				continue;
			}
			$parent_id = intval($created['term_id']);
		} else {
			$parent_id = intval($parent->term_id);
			if ($parent->name !== $parent_data['name']) {
				wp_update_term($parent_id, 'stever_place_map', array('name' => $parent_data['name']));
			}
		}

		$term_ids[$parent_slug] = $parent_id;

		foreach ($parent_data['children'] as $child_slug => $child_name) {
			$child = stever_places_find_term_for_hierarchy('stever_place_map', $child_slug, $child_name);
			if (!$child || is_wp_error($child)) {
				$created = wp_insert_term($child_name, 'stever_place_map', array(
					'slug' => $child_slug,
					'parent' => $parent_id,
				));
				if (is_wp_error($created) || !isset($created['term_id'])) {
					continue;
				}
				$child_id = intval($created['term_id']);
			} else {
				$child_id = intval($child->term_id);
				$update = array();
				if ($child->name !== $child_name) {
					$update['name'] = $child_name;
				}
				if ($child->slug !== $child_slug) {
					$update['slug'] = $child_slug;
				}
				if (intval($child->parent) !== $parent_id) {
					$update['parent'] = $parent_id;
				}
				if (!empty($update)) {
					wp_update_term($child_id, 'stever_place_map', $update);
				}
			}

			$term_ids[$child_slug] = $child_id;
		}
	}

	return $term_ids;
}

function stever_places_get_place_info_assignment_term_ids($map_slug, $child_slug = '') {
	$term_ids = stever_places_ensure_place_info_terms();
	$assignments = array();

	if (isset($term_ids[$map_slug])) {
		$assignments[] = intval($term_ids[$map_slug]);
	}

	if ('' !== $child_slug && isset($term_ids[$child_slug])) {
		$assignments[] = intval($term_ids[$child_slug]);
	}

	return array_values(array_unique(array_filter($assignments)));
}

function stever_places_seed_map_terms() {
	stever_places_ensure_place_info_terms();
}
add_action('init', 'stever_places_seed_map_terms', 20);

