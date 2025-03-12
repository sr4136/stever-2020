<?php
/**
 * stever functions and definitions
 *
 * @package stever
 */


function stever_setup(){
	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	/*
	 * Enable support for Responsive embeds.
	 *
	 * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
	 */
	add_theme_support('responsive-embeds');

	/*
	 * Menus.
	 */
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary', 'stever'),
		'social-icons' => __( 'Social', 'stever' ) // social icons in header
	) );

	/*
	 * Switch default elements' core markup to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'gallery',
		'caption',
	) );
}
add_action( 'after_setup_theme', 'stever_setup' );


/**
 * Add categories for attachments.
 *
 * @link https://premium.wpmudev.org/blog/wordpress-media-categories-tags/
 */
//
function stever_add_categories_for_attachments() {
	$labels = array(
		'name'                       => __( 'Media Categories', 'Taxonomy General Name', 'stever' ),
		'singular_name'              => __( 'Media Category', 'Taxonomy Singular Name', 'stever' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'media_cat', array( 'attachment' ), $args );
}
add_action( 'init' , 'stever_add_categories_for_attachments' );


/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function stever_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'stever' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'stever' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'stever_widgets_init' );


/**
 * Includes
 */
// Custom template tags for this theme.
require get_template_directory() . '/inc/enqueue-scripts-styles.php';
 
// Custom template tags for this theme.
require get_template_directory() . '/inc/template-tags.php';

// Quotes Post Type & Functionaltiy
require get_template_directory() .'/inc/quotes.php';

// Custom Dashboard Widgets
require get_template_directory() . '/inc/dashboard.php';

// Metabox: Body Classes
require get_template_directory() .'/inc/metabox-body-class.php';

// Shortcode: Time Since
require get_template_directory() .'/inc/shortcode-time-since.php';

// Shortcode: Logged in Content
require get_template_directory() .'/inc/shortcode-logged-in-content.php';

// Shortcode: Icons
require get_template_directory() .'/inc/shortcode-icons.php';

// Shortcode: Static Content Include
require get_template_directory() .'/inc/shortcode-static-include.php';

// Taxonomy: Workflow Statuses
require get_template_directory() .'/inc/taxonomy-workflow.php';

// Blocks: Custom Styles
require get_template_directory() .'/inc/block-styles.php';

// Siteground: Clear Cache on Theme File Change
require get_template_directory() . '/inc/siteground-clear-cache.php';