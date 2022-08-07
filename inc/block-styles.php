<?php
/**
 * Register Custom Block Styles
 */
if ( function_exists( 'register_block_style' ) ) {
    function stever_register_block_styles() {

        /* Table: Professional Development */
        register_block_style( 'core/table', array(
            'name'         => 'stever-table-pro-dev',
            'label'        => 'Prof. Dev.',
        ) );
        /* Table: Professional Development */
        register_block_style( 'core/table', array(
            'name'         => 'stever-table-books',
            'label'        => 'Books',
        ) );
        /* Table: Professional Development */
        register_block_style( 'core/table', array(
            'name'         => 'stever-table-speaking',
            'label'        => 'Speaking/Teaching',
        ) );

        /* Gallery: Fancybox */
        register_block_style( 'core/gallery', array(
            'name'         => 'fancybox',
            'label'        => 'Fancybox Gallery',
        ) );

        /* Heading: Year Divider */
        register_block_style( 'core/heading', array(
            'name'         => 'year-divider',
            'label'        => 'Year Divider',
        ) );

        /* Columns: Icon/Colors */
        register_block_style( 'core/columns', array(
            'name'         => 'stever-columns-features',
            'label'        => 'Features (Plain)',
        ) );
        register_block_style( 'core/columns', array(
            'name'         => 'stever-columns-statistics',
            'label'        => 'Statistics (Stylized)',
        ) );
	        register_block_style( 'core/column', array(
	            'name'         => 'stever-columns-statistics-pointer',
	            'label'        => 'Stats: Pointer',
	        ) );
	        register_block_style( 'core/column', array(
	            'name'         => 'stever-columns-statistics-themes',
	            'label'        => 'Stats: Themes',
	        ) );
	        register_block_style( 'core/column', array(
	            'name'         => 'stever-columns-statistics-team',
	            'label'        => 'Stats: Team',
	        ) );
	        register_block_style( 'core/column', array(
	            'name'         => 'stever-columns-statistics-commits',
	            'label'        => 'Stats: Commits',
	        ) );
		/* Indented List */
        register_block_style( 'core/list', array(
            'name'         => 'stever-list-indented',
            'label'        => 'Indented List',
        ) );
    }
    add_action( 'init', 'stever_register_block_styles' );
}