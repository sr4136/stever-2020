<?php
/**
 * Register Custom Block Styles
 */
if ( function_exists( 'register_block_style' ) ) {
    function stever_register_block_styles() {

        /* Table: Professional Development */
        register_block_style( 'core/table', array(
            'name'         => 'stever-table-pro-dev',
            'label'        => 'SteveR: Professional Development',
        ) );
        /* Table: Professional Development */
        register_block_style( 'core/table', array(
            'name'         => 'stever-table-books',
            'label'        => 'SteveR: Books',
        ) );
        /* Table: Professional Development */
        register_block_style( 'core/table', array(
            'name'         => 'stever-table-speaking',
            'label'        => 'SteveR: Speaking',
        ) );

        /* Gallery: Fancybox */
        register_block_style( 'core/gallery', array(
            'name'         => 'fancybox',
            'label'        => 'SteveR: Fancybox Gallery',
        ) );

        /* Heading: Year Divider */
        register_block_style( 'core/heading', array(
            'name'         => 'year-divider',
            'label'        => 'SteveR: Year Divider',
        ) );

        /* Columns: Icon/Colors */
        register_block_style( 'core/columns', array(
            'name'         => 'stever-columns-features',
            'label'        => 'SteveR: Features',
        ) );
        register_block_style( 'core/columns', array(
            'name'         => 'stever-columns-statistics',
            'label'        => 'SteveR: Statistics',
        ) );
	        register_block_style( 'core/column', array(
	            'name'         => 'stever-columns-statistics-pointer',
	            'label'        => 'SteveR: Statistics - Pointer',
	        ) );
	        register_block_style( 'core/column', array(
	            'name'         => 'stever-columns-statistics-themes',
	            'label'        => 'SteveR: Statistics - Themes',
	        ) );
	        register_block_style( 'core/column', array(
	            'name'         => 'stever-columns-statistics-team',
	            'label'        => 'SteveR: Statistics - Team',
	        ) );
	        register_block_style( 'core/column', array(
	            'name'         => 'stever-columns-statistics-commits',
	            'label'        => 'SteveR: Statistics - Commits',
	        ) );

    }
    add_action( 'init', 'stever_register_block_styles' );
}