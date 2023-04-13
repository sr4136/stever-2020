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
        /* Table: Books */
        register_block_style( 'core/table', array(
            'name'         => 'stever-table-books',
            'label'        => 'Books',
        ) );
        /* Table: Speaking/Teaching */
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
        /* Columns: Event Notes Header */
        register_block_style('core/columns', array(
            'name'         => 'stever-columns-event-notes',
            'label'        => 'Event Notes Header',
        ));

		/* Indented List */
        register_block_style( 'core/list', array(
            'name'         => 'stever-list-indented',
            'label'        => 'Indented List',
        ) );

        /* Gallery: Fancybox */
        register_block_style('core/quote', array(
            'name'         => 'attention',
            'label'        => 'Attention',
        ));

        /* Lists: Extra Vertical Spacing */
        register_block_style('core/list', array(
            'name'         => 'extra-vertical-spacing',
            'label'        => 'Extra Vertical Spacing',
        ));

        /* Lists: Extra Vertical Spacing */
        register_block_style('core/heading', array(
            'name'         => 'no-spacing-above',
            'label'        => 'No Vertical Space Above',
        ));

        /* Lists: Extra Vertical Spacing */
        register_block_style('core/query', array(
            'name'         => 'zebra-stripe',
            'label'        => 'Zebra Striped',
        ));

    }
    add_action( 'init', 'stever_register_block_styles' );
}