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


		// Group: half-rounded
		register_block_style('core/group', array(
			'name'  => 'stever-attention-half-rounded',
			'label' => 'Attention: Half Rounded',
		));
		// Group: grid-internal
		register_block_style('core/group', array(
			'name'  => 'stever-grid-internal',
			'label' => 'Grid: Internal',
		));


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

        /* Quote: Attention*/
        register_block_style('core/quote', array(
            'name'         => 'attention',
            'label'        => 'Attention',
        ));
        /* Quote: Graphic*/
        register_block_style('core/quote', array(
            'name'         => 'graphic',
            'label'        => 'Graphic',
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


// Add custom color palette to the theme
function stever_register_block_color_palette() {
	// Define your custom colors
	$colors = array(
		array(
			'name'  => 'Green',
			'slug'  => 'green',
			'color' => '#6b7d05',
		),
		array(
			'name'  => 'Bright Green',
			'slug'  => 'green-bright',
			'color' => '#8b9b31',
		),
		array(
			'name'  => 'Brown',
			'slug'  => 'brown',
			'color' => '#332012',
		),
		array(
			'name'  => 'Red',
			'slug'  => 'red',
			'color' => '#c2552e',
		),
		array(
			'name'  => 'White',
			'slug'  => 'white',
			'color' => '#ffffff',
		),
		array(
			'name'  => 'Gray',
			'slug'  => 'gray',
			'color' => '#5f6b7e',
		),
		array(
			'name'  => 'Light Gray',
			'slug'  => 'gray-light',
			'color' => '#f7f7f7',
		),
		array(
			'name'  => 'Medium Gray',
			'slug'  => 'gray-med',
			'color' => '#cccccc',
		),
		array(
			'name'  => 'Dark Gray',
			'slug'  => 'gray-dark',
			'color' => '#666666',
		),
		array(
			'name'  => 'Accent Green',
			'slug'  => 'accent-green',
			'color' => '#516322',
		),
		array(
			'name'  => 'Accent Teal',
			'slug'  => 'accent-teal',
			'color' => '#416e82',
		),
		array(
			'name'  => 'Accent Red',
			'slug'  => 'accent-red',
			'color' => '#814519',
		),
		array(
			'name'  => 'Accent Blue',
			'slug'  => 'accent-blue',
			'color' => '#3f506c',
		),
	);

	// Add the color palette
	add_theme_support('editor-color-palette', $colors);
}

// Hook into the 'after_setup_theme' action
add_action('after_setup_theme', 'stever_register_block_color_palette');