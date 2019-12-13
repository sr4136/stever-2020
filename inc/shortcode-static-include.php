<?php
/* Static File */
function stever_static_shortcode( $atts ) {
	$file = $atts['file'];
	$fullPath = get_stylesheet_directory_uri() . '/static/' . $file;

	$fileContent = file_get_contents( $fullPath );

	return $fileContent;
}
add_shortcode( 'static', 'stever_static_shortcode' );