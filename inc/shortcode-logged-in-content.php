<?php
/**
 * Shortcode: if user is logged in, display supplied content
 */

function sr_logged_in_content( $atts, $content = '' ) {
	if( is_user_logged_in() ){
		return $content;
	}
}
add_shortcode( 'logged-in-content', 'sr_logged_in_content' );
