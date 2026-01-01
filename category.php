<?php

/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package stever
 */

get_header();
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main">

		<header class="page-header">
			<?php the_archive_title('<h1 class="page-title">Posts ', '</h1>'); ?>
		</header><!-- .page-header -->

		<?php if (have_posts()) : ?>
			<div class="archive-listing is-style-zebra-striped">
				<?php
				while (have_posts()) :
					the_post();
					get_template_part('template-parts/content', get_post_type());
				endwhile;

				the_posts_navigation();
				?>
			</div>
		<?php else: ?>
			<?php get_template_part('template-parts/content', 'none'); ?>
		<?php endif; ?>

	</main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();
