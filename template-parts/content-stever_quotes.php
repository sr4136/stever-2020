<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package stever
 */

$content = get_the_content();
$content_length = strlen( get_the_content() );
$content_size_string = null;
if( $content_length >= 600 ){
	$content_size = 'clLarge';
}

?>

<article id="post-<?php the_ID(); ?>" data-contentLength="<?php echo $content_length; ?>"<?php echo $content_size_string; ?><?php post_class( $content_size ); ?>>
	<header class="entry-header">
		<?php

		/* if ( 'post' === get_post_type() ) :
			?>
			<div class="entry-meta">
				<?php
				stever_posted_on();
				stever_posted_by();
				?>
			</div><!-- .entry-meta -->
		<?php endif; ?>
		*/
		?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php
		the_content( sprintf(
			wp_kses(
				/* translators: %s: Name of current post. Only visible to screen readers */
				__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'stever' ),
				array(
					'span' => array(
						'class' => array(),
					),
				)
			),
			get_the_title()
		) );

		wp_link_pages( array(
			'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'stever' ),
			'after'  => '</div>',
		) );
		?>
	</div><!-- .entry-content -->
	<!
	<footer class="entry-footer">
		<?php // stever_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->
