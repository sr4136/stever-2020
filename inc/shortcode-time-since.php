<?php
/*
 * Time Since Shortcode
 * ex: "With [time since="1997"] years experience."
 * outputs: "With 20 years of experience"
 *
 * From: https://gist.github.com/ThatGuySam/233b5ba488d74e8cba9787d252da0c5f
 */


function stever_time_since_shortcode( $atts ) {

		extract( shortcode_atts( array(
			'since' => "",
		), $atts, 'time' ) );

		$since_timestamp = strtotime( $since );

		// Then
		$then = new DateTime( date( 'Y-m-d', $since_timestamp ) );
		// Now
		$now = new DateTime( date( 'Y-m-d' ) );

		$diff = $now->diff( $then );

		// https://gist.github.com/Victa/3523765
		// Get the difference in the unit specified (years by default)
		$output = $diff->{y} . ' years';
		if( 0 !== $diff->{m} ){
			$output .= ' and ' . $diff->{m} . ' month';
			if( 1 !== $diff->{m} ) {
				$output .= 's';
			}
		}

		return $output;
	}

add_shortcode( 'time', 'stever_time_since_shortcode') ;
