<?php
/**
 * stever functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package stever
 */

if ( ! function_exists( 'stever_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function stever_setup()
	{
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on stever, use a find and replace
		 * to change 'stever' to the name of your theme in all the template files.
		 */
		load_theme_textdomain('stever', get_template_directory() . '/languages');

		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support('title-tag');

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support('post-thumbnails');

		/*
		 * Menus.
		 */
		register_nav_menus(array(
			'primary' => esc_html__('Primary', 'stever'),
			'social-icons' => __('Social', 'bonestheme') // social icons in header
		));

		/*
		 * Switch default elements' core markup to output valid HTML5.
		 */
		add_theme_support('html5', array(
			'search-form',
			'gallery',
			'caption',
		));

	}

endif;
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
 * Enqueue scripts and styles.
 */
function stever_scripts() {

	/* Fonts */
	wp_enqueue_style( 'googleFonts-Lato', '//fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic' );
	wp_enqueue_style( 'googleFonts-Merriweather', '//fonts.googleapis.com/css?family=Merriweather:400,700,400italic,700italic' );

	wp_enqueue_style( 'fontawesome',
		get_stylesheet_directory_uri() . '/css/fontawesome-free-5.8.1-web/css/all.min.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/fontawesome-free-5.8.1-web/css/all.min.css' )
	);

	/* CSS: Underscores */
	wp_enqueue_style( 'stever-underscores',
		get_stylesheet_directory_uri() . '/css/underscores.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/underscores.css' )
	);

	/* CSS: Foundation */
	wp_enqueue_style( 'stever-foundation',
		get_stylesheet_directory_uri() . '/css/foundation.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/foundation.css' )
	);

	/* CSS: SteveR Theme Styles */
	wp_enqueue_style( 'stever-style',
		get_stylesheet_directory_uri() . '/style.css',
		array(),
		filemtime( get_stylesheet_directory() . '/style.css' )
	);

	/* CSS: SteveR Blocks Styles */
	wp_enqueue_style( 'stever-blocks-front',
		get_stylesheet_directory_uri() . '/css/blocks.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/blocks.css' )
	);

	wp_enqueue_script( 'stever-skip-link-focus-fix',
        get_template_directory_uri() . '/js/skip-link-focus-fix.js',
        array(),
        filemtime( get_stylesheet_directory() . '/js/skip-link-focus-fix.js' ),
        true
    );

	/* Use the "Body Class" metabox as a vehicle to enqueue scripts/styles */
	$stever_body_class_text = get_post_meta( get_the_id(), 'stever_body_class_text', true );

    /* CSS & JS: Tipped */
    if (
        strpos( $stever_body_class_text, 'sr-table-pro-dev' ) !== false ||
        strpos( $stever_body_class_text, 'sr-bu-timeline' ) !== false
    ) {
        wp_enqueue_style( 'stever-tipped',
            get_stylesheet_directory_uri() . '/js/tipped/tipped.css',
            array(),
            filemtime( get_stylesheet_directory() . '/js/tipped/tipped.css' )
        );
        wp_enqueue_script( 'stever-tipped',
            get_template_directory_uri() . '/js/tipped/tipped.min.js',
            array(),
            filemtime( get_stylesheet_directory() . '/js/tipped/tipped.min.js' ),
            true
        );
        wp_enqueue_script( 'stever-tipped-kickoff',
            get_template_directory_uri() . '/js/tipped/tipped-kickoff.js',
            array(),
            filemtime( get_stylesheet_directory() . '/js/tipped/tipped-kickoff.js' ),
            true
        );
    }

	// Professional Development Page.
    if ( strpos( $stever_body_class_text, 'sr-table-pro-dev' ) ){
		wp_enqueue_script( 'stever-table-filter',
            get_template_directory_uri() . '/js/table-filter.js',
            array(),
            filemtime( get_stylesheet_directory() . '/js/table-filter.js' ),
            true
        );
	}

}
add_action( 'wp_enqueue_scripts', 'stever_scripts' );


function stever_admin_scripts() {
	/* CSS: Admin General */
	wp_enqueue_style( 'stever-admin',
		get_stylesheet_directory_uri() . '/css/admin.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/admin.css' )
	);

    /* CSS: SteveR Blocks Styles */
	wp_enqueue_style( 'stever-blocks',
		get_stylesheet_directory_uri() . '/css/blocks.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/blocks.css' )
	);
}
add_action( 'admin_enqueue_scripts', 'stever_admin_scripts' );

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Quotes Post Type & Functionaltiy
 */
require get_template_directory() .'/inc/quotes.php';

/**
 * Custom Dashboard Widgets
 */
require get_template_directory() . '/inc/dashboard.php';

/**
 * Metabox: Body Classes
 */
require get_template_directory() .'/inc/metabox-body-class.php';

/**
 * Shortcode: Time Since
 */
require get_template_directory() .'/inc/shortcode-time-since.php';

/**
 * Shortcode: Logged in Content
 */
require get_template_directory() .'/inc/shortcode-logged-in-content.php';

/**
 * Shortcode: Icons
 */
require get_template_directory() .'/inc/shortcode-icons.php';

/**
 * Shortcode: Posts with Image
 */
require get_template_directory() .'/inc/shortcode-posts-with-image.php';


/**
 * Shortcode: Static Content Include
 */
require get_template_directory() .'/inc/shortcode-static-include.php';