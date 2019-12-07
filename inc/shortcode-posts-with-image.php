<?php
/**
 * Shortcode: Posts with Featured Image
 * @package SteveRudolfi
 */

function stever_posts_with_image( $atts ) {
	$args = array(
		'post_type'			=> 'post',
		'posts_per_page'	=> 50,
		'orderby'			=> 'title',
		'order'				=> 'ASC',
		'post_status'		=> 'publish',
	);
	
	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() ) {
		ob_start();
			echo '<ul class="posts-with-image">';
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$img_class = '';
				if( ! get_the_post_thumbnail() ){
					$img_class = ' class="no-image"';
				}
				echo '<li' . $img_class . '>';
					echo '<a href="' . get_the_permalink() . '">';
						echo '<figure>';
							echo the_post_thumbnail();
						echo '</figure>';
						echo get_the_title();
					echo '</a>';
				echo '</li>';
			}
			echo '</ul>';

			wp_reset_postdata();
		return( ob_get_clean() );
	}
}
add_shortcode( 'posts_with_image', 'stever_posts_with_image' );
