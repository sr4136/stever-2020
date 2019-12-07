<?php
/**
 * Shortcode: Icons
 * @package SteveRudolfi
 */

function stever_shortcode_icons( $atts ) {
	$type = $atts[0];

	if( $type ){
		$labels = array(
			'book' => array(
				'label' => 'Book',
				'class' => 'book',
			),
			'conference' => array(
				'label' => 'Conference',
				'class' => 'users',
			),
			'meetup' => array(
				'label' => 'Meetup',
				'class' => 'lightbulb',
			),
			'podcast' => array(
				'label' => 'Podcast',
				'class' => 'headphones',
			),
			'remote' => array(
				'label' => 'Conference (Remote Participation)',
				'class' => 'laptop',
			),
			'training' => array(
				'label' => 'Training',
				'class' => 'graduation-cap',
			),
			'webinar' => array(
				'label' => 'Webinar',
				'class' => 'keyboard',
			),
		);

		if( $labels[ $type ] ){
			return ( "<i title='{$labels[ $type ]['label']}' class='fas fa-{$labels[ $type ]['class']}'></i>" );
		}

	}
	return;
}
add_shortcode( 'i', 'stever_shortcode_icons' );
