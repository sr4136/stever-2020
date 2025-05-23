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

		<?php if (have_posts()) : ?>

			<header class="page-header">
				<?php the_archive_title('<h1 class="page-title">', '</h1>'); ?>
				<a class="button button-ghost" href="https://steverudolfi.com/quotes/?layout=slideshow">View as a slideshow</a>
			</header><!-- .page-header -->

		<?php

			while (have_posts()) :
				the_post();

				get_template_part('template-parts/content', get_post_type());
			endwhile;

			the_posts_navigation();

		else :

			get_template_part('template-parts/content', 'none');

		endif;
		?>

	</main><!-- #main -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();
