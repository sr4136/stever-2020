<?php
/**
 * Custom taxonomy for workflow statuses
 */

function stever_workflow() {

	$labels = array(
		'name'				=> _x( 'Workflow Status', 'Taxonomy General Name', 'stever' ),
		'singular_name'		=> _x( 'Workflow Status', 'Taxonomy Singular Name', 'stever' ),
		'menu_name'			=> __( 'Workflow Status', 'stever' ),
	);
	$args = array(
		'labels'			=> $labels,
		'hierarchical'		=> true,
		'public'			=> true,
		'show_ui'			=> true,
		'show_admin_column'	=> true,
		'show_in_rest'		=> true,
	);
	register_taxonomy( 'stever_workflow', array( 'post', 'page' ), $args );

}
add_action( 'init', 'stever_workflow', 0 );