<?php
/**
 * "Quotes" Custom post type, javascript, GET params, columns, etc.
 */
function stever_cpt_quotes() {
	$labels = array(
		'name'                  => 'Quotes',
		'singular_name'         => 'Quote',
		'menu_name'             => 'Quotes',
	);
	$args = array(
		'label'                 => 'Quote',
		'description'           => 'Post Type Description',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'revisions' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-editor-quote',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
		'show_in_rest'          => true,
		/* For ordering the quotes in the edit screen, so I can keep the "quotes to add" at the top, 'page-attributes' here */
		'supports'				=> array( 'title', 'editor', 'page-attributes' ),
		'rewrite'	            => array(
			'slug'		 => 'quotes',
			'with_front' => false
		),
		/* Always ensure a quote block exists on "new quote" */
		'template'              => array(
			array( 'core/quote' )
		),
	);
	register_post_type( 'stever_quotes', $args );
}
add_action( 'init', 'stever_cpt_quotes', 0 );

/* Modify Archive Page Title */
function stever_titles( $title ) {
	if( is_post_type_archive( 'stever_quotes' ) ){
		return 'Quotes';
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'stever_titles', 10, 2 );

/* Query Modifications */
function stever_quotes_query_mods( $query ) {
    if( is_post_type_archive( 'stever_quotes' ) ){
	    $query->set( 'posts_per_page', 500 );

		if( isset( $_GET[ 'orderby' ] ) ){
			$order = $_GET[ 'orderby' ];
			if( 'random' == $order ){
				$query->set( 'orderby', 'rand' );
			}
		}
    }
}
add_filter( 'pre_get_posts', 'stever_quotes_query_mods' );

/* Add JavaScript */
function stever_quote_slideshow_script() {
	if( is_post_type_archive( 'stever_quotes' ) ){
		wp_enqueue_script( 'stever-quotes-slideshow', get_template_directory_uri() . '/js/quotes-slideshow.js', array(), '', true );
	}
}
add_filter( 'wp_enqueue_scripts', 'stever_quote_slideshow_script' );

/* Custom column + content */
function stever_custom_quotes_column( $columns ) {
    $columns[ 'stever_quote_text' ] = 'Quote Text';
    unset( $columns[ 'date' ] );
    unset( $columns[ 'title' ] );
    return $columns;
}
add_action( 'manage_stever_quotes_posts_columns' , 'stever_custom_quotes_column', 10, 2 );

function stever_custom_quotes_column_content( $column, $post_id ) {
    if ( $column == 'stever_quote_text' ){
	    $the_content = get_the_content();
		echo( preg_replace( "/<a[^>]+\>[a-z]+/i", "", $the_content ) );
    }
}
add_action( 'manage_stever_quotes_posts_custom_column' , 'stever_custom_quotes_column_content', 10, 2 );

/* Remove `private` posts from query */
function stever_quotes_query( $query ) {
    if ( !is_admin() && $query ){
	    $query_vars = $query->query_vars;
	    $post_type = null;
		if( array_key_exists( 'post_type', $query_vars ) ){
		    if( 'stever_quotes' == $query_vars[ 'post_type' ] ) {
				$query->set( 'post_status', 'publish' );
			}
		}
    }
}
add_action( 'pre_get_posts', 'stever_quotes_query' );


/* Add links to the WP Admin bar */
function stever_quotes_in_adminbar() {
	global $wp_admin_bar;

	// Backend.
	if( function_exists( 'get_current_screen' ) ){
		$current_screen = get_current_screen()->id;

		if( 'stever_quotes' == $current_screen ){
			$args = array(
				'id'    => 'stever-quotes-editall',
				'title' => 'View Quotes',
				'href'  => get_post_type_archive_link( 'stever_quotes' ),
				'meta'  => array(
					'class' => 'stever-quotes-editall',
					'title' => 'View Quotes'
				)
			);
			$wp_admin_bar->add_menu( $args );
		}
	}

	// Frontend.
	if( !is_admin() && is_post_type_archive( 'stever_quotes' ) ){
		$args = array(
			'id'    => 'stever-quotes-editall',
			'title' => 'Edit All Quotes',
			'href'  => get_admin_url( null, 'edit.php?post_type=stever_quotes' ),
			'meta'  => array(
				'class' => 'stever-quotes-editall',
				'title' => 'Edit All Quotes'
			)
		);
		$wp_admin_bar->add_node( $args );

		$args = array(
			'id'    => 'stever-quotes-new',
			'title' => 'Add New Quote',
			'href'  => get_admin_url( null, 'post-new.php?post_type=stever_quotes' ),
			'meta'  => array(
				'class' => 'stever-quotes-new',
				'title' => 'Add New Quote'
			)
		);
		$wp_admin_bar->add_node( $args );
	}
}
add_action( 'wp_before_admin_bar_render', 'stever_quotes_in_adminbar', 999 );
