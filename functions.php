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
	function stever_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on stever, use a find and replace
		 * to change 'stever' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'stever', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

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

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'menu-1' => esc_html__( 'Primary', 'stever' ),
			'social-icons' => __( 'Social Icons', 'bonestheme' ) // social icons in header
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'stever_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		) );
	}
endif;
add_action( 'after_setup_theme', 'stever_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function stever_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'stever_content_width', 640 );
}
add_action( 'after_setup_theme', 'stever_content_width', 0 );

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
	wp_enqueue_style( 'stever-blocks',
		get_stylesheet_directory_uri() . '/css/blocks.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/blocks.css' )
	);

	wp_enqueue_script( 'stever-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20151215', true );

	wp_enqueue_script( 'stever-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'stever_scripts' );


function stever_admin_scripts() {
    /* CSS: SteveR Blocks Styles */
	wp_enqueue_style( 'stever-blocks',
		get_stylesheet_directory_uri() . '/css/blocks.css',
		array(),
		filemtime( get_stylesheet_directory() . '/css/blocks.css' )
	);
}
add_action( 'admin_enqueue_scripts', 'stever_admin_scripts' );



/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';


/**
 * Custom Dashboard Widgets
 */
require get_template_directory() . '/inc/dashboard.php';

/**
 * Shortcode: Time Since
 */
require get_template_directory() .'/inc/time-since.php';

