<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package stever
 */

get_header();
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">

			<section class="grid-x">

				<header class="page-header cell">
					<h1 class="page-title"><?php esc_html_e( 'Not found!', 'stever' ); ?></h1>
				</header><!-- .page-header -->

				<div class="page-content cell medium-8">

					<p><?php esc_html_e( "It looks like nothing exists at this URL. Maybe try a search?", 'stever' ); ?></p>

					<form role="search" method="get" class="search-form grid-x row" action="https://steverudolfi.com/">
						<label class="cell medium-10">
							<span class="screen-reader-text">Search for:</span>
							<input type="search" class="search-field" placeholder="Type search here..." value="" name="s">
						</label>
						<div class="cell medium-2">
							<input type="submit" class="search-submit button-ghost cell medium-1" value="Search">
						</div>
					</form>

                    <img alt="Snarky 404 Image" src="<?php echo get_stylesheet_directory_uri(); ?>/img/404.jpg" />

				</div><!-- .page-content -->

				<?php
					$args = array(
						'post_type' => 'page',
						'orderby' => 'menu_order',
						'order' => 'ASC',
						'post_status' => 'publish',
						'has_password' => false,
						'meta_query' => array(
							'relation' => 'OR',
							// Doesn't exist (Yoast defaults to 'show').
							array(
								'key' => '_yoast_wpseo_meta-robots-noindex',
								'compare' => 'NOT EXISTS'
							),
							// Is set to "no". 0 = default; 1 = no; 2 = yes;
							array(
								'key' => '_yoast_wpseo_meta-robots-noindex',
								'value' => '1',
								'compare' => '!=',
							)
						)
					);
				?>

				<aside class="cell medium-4">
					<div class="all-pages">
						<h3>All pages:</h3>
						<ul>
							<?php
								$pages_query = new WP_Query( $args );

								if ($pages_query->have_posts()) {
									while ($pages_query->have_posts()) {
										$pages_query->the_post();
										echo '<li><a href="' . get_the_permalink() . '">' . get_the_title() . '</a> </li> ';
									}
								}

								wp_reset_postdata();
							?>
						</ul>
					</div>
					<div class="recent-posts">
						<h3>Recent Posts</h3>
						<ul>
							<?php
								$args['post_type'] = 'post';
								$args['orderby'] = 'date';
								$posts_query = new WP_Query( $args );

								if ($posts_query->have_posts()) {
									while ($posts_query->have_posts()) {
									$posts_query->the_post();
										echo '<li><a href="' . get_the_permalink() . '">' . get_the_title() . '</a> </li> ';
									}
								}
								
								wp_reset_postdata();
							?>
						</ul>
					</div>
				</aside>
			</section><!-- .error-404 -->
		</main><!-- #main -->
	</div><!-- #primary -->
<?php get_footer();
