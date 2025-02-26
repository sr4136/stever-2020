<?php
/**
 * Shortcode: Icons
 * @package stever
 */

function stever_shortcode_icons( $atts ) {
	$type = $atts[0];

	if( $type ){
		$labels = array(
			'read' => array(
				'label' => 'Read Books/Articles',
				'class' => 'book',
			),
			'training' => array(
				'label' => 'Training',
				'class' => 'graduation-cap',
			),
			'conference' => array(
				'label' => 'Conference',
				'class' => 'users',
			),
			'meetup' => array(
				'label' => 'Meetup',
				'class' => 'lightbulb',
			),
			'remote' => array(
				'label' => 'Conference (Remote Participation)',
				'class' => 'laptop',
			),
			'webinar' => array(
				'label' => 'Remote Webinar/Meetup/Podcast',
				'class' => 'keyboard',
			),
		);

		if( $labels[ $type ] ){
			return ( "<i data-title='{$labels[ $type ]['label']}' class='fas fa-{$labels[ $type ]['class']}'></i>" );
		}

	}
	return;
}
add_shortcode( 'i', 'stever_shortcode_icons' );
