<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package stever
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if (is_singular()) : ?>
		<header class="entry-header">
			<?php the_title('<h1 class="entry-title">', '</h1>'); ?>
		</header><!-- .entry-header -->
		<div class="entry-content">
			<?php
			the_content(sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__('Continue reading<span class="screen-reader-text"> "%s"</span>', 'stever'),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			));

			?>
		</div><!-- .entry-content -->
	<?php else : ?>
		<div class="wp-block-query">
			<div class="wp-block-columns are-vertically-aligned-center is-layout-flex wp-block-columns-is-layout-flex">
				<div class="wp-block-column is-vertically-aligned-center is-layout-flow wp-block-column-is-layout-flow" style="flex-basis:75%">
					<h2 class="wp-block-post-title">
						<a href="<?php echo esc_url(get_permalink()) ?>" target="_self">
							<?php the_title(); ?>
						</a>
					</h2>

					<div class="wp-block-post-excerpt">
						<?php the_excerpt(); ?>
					</div>
				</div>

				<div class="wp-block-column is-vertically-aligned-center is-layout-flow wp-block-column-is-layout-flow" style="flex-basis:25%">
					<?php if (has_post_thumbnail()): ?>
						<figure class="wp-block-post-featured-image"><a href="https://steverudolfi.com/posts/book-notes-when-were-in-charge/" target="_self">
								<a href="<?php echo esc_url(get_permalink()) ?>" target="_self"><?php the_post_thumbnail('medium'); ?></a>
						</figure>
					<?php endif; ?>
				</div>
			</div> <!-- .wp-block-columns -->
		</div><!-- .entry-content -->
	<?php endif; ?>



</article><!-- #post-<?php the_ID(); ?> -->