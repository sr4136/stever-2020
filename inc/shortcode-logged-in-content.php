<?php
/**
 * Shortcode: if user is logged in, display supplied content
 */

function sr_logged_in_content( $atts, $content = '' ) {
	$atts = is_array( $atts ) ? $atts : array();
	$atts = shortcode_atts(
		array(
			'id'    => '',
			'class' => '',
		),
		$atts,
		'logged-in-content'
	);

	if( is_user_logged_in() ){
		$custom_classes = preg_split( '/\s+/', trim( (string) $atts['class'] ) );
		$custom_classes = array_filter( $custom_classes );
		$lower_classes = array_map( 'strtolower', $custom_classes );

		$all_classes = array( 'logged-in-only' );
		foreach ( $custom_classes as $class_name ) {
			$sanitized_class = sanitize_html_class( $class_name );
			if ( ! empty( $sanitized_class ) ) {
				$all_classes[] = $sanitized_class;
			}
		}

		$class_attr = implode( ' ', array_unique( $all_classes ) );
		$id_attr = ! empty( $atts['id'] ) ? ' id="' . esc_attr( $atts['id'] ) . '"' : '';
		$output = '<div class="' . esc_attr( $class_attr ) . '"' . $id_attr . '>' . $content . '</div>';

		return $output;
	}
}
add_shortcode( 'logged-in-content', 'sr_logged_in_content' );

