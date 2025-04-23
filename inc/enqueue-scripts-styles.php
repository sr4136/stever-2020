<?php

/**
 * Enqueue: Scripts & Styles - Frontend & Admin.
 * @package stever
 */


/**
 * Frontend Enqueue
 */
function stever_scripts_styles() {

	// Use the "Body Class" metabox as a vehicle to enqueue scripts/styles.
	$stever_body_class_text = get_post_meta(get_the_id(), 'stever_body_class_text', true);

	// Fonts.
	wp_enqueue_style('bunnyFonts', '//fonts.bunny.net/css?family=b612-mono:400,400i,700,700i|lato:400,400i,700,700i|roboto:400,400i,700,700i');

	// Icons.
	wp_enqueue_style(
		'fontawesome',
		get_stylesheet_directory_uri() . '/css/fontawesome-free-5.8.1-web/css/all.min.css',
		array(),
		filemtime(get_stylesheet_directory() . '/css/fontawesome-free-5.8.1-web/css/all.min.css')
	);

	// CSS: Underscores.
	wp_enqueue_style(
		'stever-underscores',
		get_stylesheet_directory_uri() . '/css/underscores.css',
		array(),
		filemtime(get_stylesheet_directory() . '/css/underscores.css')
	);

	// CSS: Foundation.
	wp_enqueue_style(
		'stever-foundation',
		get_stylesheet_directory_uri() . '/css/foundation.css',
		array(),
		filemtime(get_stylesheet_directory() . '/css/foundation.css')
	);

	// CSS: SteveR Theme Styles. 
	wp_enqueue_style(
		'stever-style',
		get_stylesheet_directory_uri() . '/style.min.css',
		array(),
		filemtime(get_stylesheet_directory() . '/style.min.css')
	);

	// CSS: SteveR Blocks Styles.
	wp_enqueue_style(
		'stever-blocks-front',
		get_stylesheet_directory_uri() . '/css/blocks.min.css',
		array(),
		filemtime(get_stylesheet_directory() . '/css/blocks.min.css')
	);

	// JS: Fix "skip-link focus".
	wp_enqueue_script(
		'stever-skip-link-focus-fix',
		get_template_directory_uri() . '/js/skip-link-focus-fix.js',
		array(),
		filemtime(get_stylesheet_directory() . '/js/skip-link-focus-fix.js'),
		true
	);

	// CSS & JS: Tipped.
	if (
		strpos($stever_body_class_text, 'sr-page-table-prodev') !== false ||
		strpos($stever_body_class_text, 'sr-bu-timeline') !== false ||
		strpos($stever_body_class_text, 'sr-page-table-books') !== false
	) {
		wp_enqueue_style(
			'stever-tipped',
			get_stylesheet_directory_uri() . '/js/tipped/tipped.css',
			array(),
			filemtime(get_stylesheet_directory() . '/js/tipped/tipped.css')
		);
		wp_enqueue_script(
			'stever-tipped',
			get_template_directory_uri() . '/js/tipped/tipped.min.js',
			array(),
			filemtime(get_stylesheet_directory() . '/js/tipped/tipped.min.js'),
			true
		);
		wp_enqueue_script(
			'stever-tipped-kickoff',
			get_template_directory_uri() . '/js/tipped/tipped-kickoff.js',
			array(),
			filemtime(get_stylesheet_directory() . '/js/tipped/tipped-kickoff.js'),
			true
		);
		wp_enqueue_script(
			'stever-dragscroll',
			get_template_directory_uri() . '/js/dragscroll/dragscroll.js',
			array(),
			filemtime(get_stylesheet_directory() . '/js/dragscroll/dragscroll.js'),
			true
		);
	}

	// CSS & JS: Fancybox (for Galleries).
	$has_gallery = has_block('gallery', get_the_id());
	if ($has_gallery) {
		wp_enqueue_style(
			'stever-fancybox',
			get_stylesheet_directory_uri() . '/js/fancybox/jquery.fancybox.min.css',
			array(),
			filemtime(get_stylesheet_directory() . '/js/fancybox/jquery.fancybox.min.css')
		);
		wp_enqueue_script(
			'stever-fancybox',
			get_template_directory_uri() . '/js/fancybox/jquery.fancybox.min.js',
			array('jquery'),
			filemtime(get_stylesheet_directory() . '/js/fancybox/jquery.fancybox.min.js'),
			true
		);
		wp_enqueue_script(
			'stever-fancybox-init',
			get_template_directory_uri() . '/js/fancybox.js',
			array('jquery', 'stever-fancybox'),
			filemtime(get_stylesheet_directory() . '/js/fancybox.js'),
			true
		);
	}
}
add_action('wp_enqueue_scripts', 'stever_scripts_styles');


/**
 * Admin
 */
function stever_admin_scripts_styles() {
	// CSS: Admin General.
	wp_enqueue_style(
		'stever-admin',
		get_stylesheet_directory_uri() . '/css/admin.min.css',
		array(),
		filemtime(get_stylesheet_directory() . '/css/admin.min.css')
	);

	// Icons.
	wp_enqueue_style(
		'fontawesome',
		get_stylesheet_directory_uri() . '/css/fontawesome-free-5.8.1-web/css/all.min.css',
		array(),
		filemtime(get_stylesheet_directory() . '/css/fontawesome-free-5.8.1-web/css/all.min.css')
	);
}
add_action('admin_enqueue_scripts', 'stever_admin_scripts_styles');

/**
 * Block admin
 */
function stever_block_styles() {
	if (is_admin()) {
		wp_enqueue_style(
			'stever-blocks',
			get_stylesheet_directory_uri() . '/css/blocks.min.css',
			array(),
			filemtime(get_stylesheet_directory() . '/css/blocks.min.css')
		);
	}
	if (is_admin()) {
		wp_enqueue_style(
			'stever-admin-blocks',
			get_stylesheet_directory_uri() . '/css/admin-blocks.min.css',
			array(),
			filemtime(get_stylesheet_directory() . '/css/admin-blocks.min.css')
		);
	}
}
add_action('enqueue_block_assets', 'stever_block_styles');


/**
 * Custom Favicon for Admin & Login.
 */
function stever_admin_favicon() {
	$favicon_url = get_stylesheet_directory_uri() . '/img/favicon-admin.ico';
	echo ('<link rel="shortcut icon" href="' . $favicon_url . '?' . uniqid() . '" />');
}
add_action('login_head', 'stever_admin_favicon');
add_action('admin_head', 'stever_admin_favicon');
