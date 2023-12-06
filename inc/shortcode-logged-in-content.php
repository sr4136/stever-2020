<?php
/**
 * Shortcode: if user is logged in, display supplied content
 */

function sr_logged_in_content( $atts, $content = '' ) {
	if( is_user_logged_in() ){
		$sc_id = ($atts['id']) ? $atts['id'] : null;
		return '<div class="logged-in-only" id="' . $sc_id . '">' . $content . '</div>';
	}
}
add_shortcode( 'logged-in-content', 'sr_logged_in_content' );
