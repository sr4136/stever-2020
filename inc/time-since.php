<?php
// From: https://gist.github.com/ThatGuySam/233b5ba488d74e8cba9787d252da0c5f
// ex:
// With [time since="1997"] years experience
// outputs: With 20 years of experience

class SCCTimeShortcode {
	static $add_script;

	static function init() {
		add_shortcode('time', array(__CLASS__, 'handle_shortcode'));
	}

	static function handle_shortcode($atts) {

		extract( shortcode_atts( array(
			'since' => "",
			'in' => 'y'
		), $atts, 'time' ) );

		$since_timestamp = strtotime($since);

		// Then
		$then = new DateTime(date( 'Y-m-d', $since_timestamp ));
		// Now
		$now = new DateTime(date( 'Y-m-d' ));

		$diff = $now->diff( $then );

		// https://gist.github.com/Victa/3523765
		// Get the difference in the unit specified(years by default)
		$output = $diff->{$in};

		return $output;
	}
}

SCCTimeShortcode::init();