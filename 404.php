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

				<aside class="cell medium-4">
					<div class="all-pages">
						<h3>All pages:</h3>
						<ul>
							<?php
							wp_list_pages( array(
								'title_li' => null,
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
							) );
							?>
						</ul>
					</div>
					<div class="recent-posts">
						<h3>Recent Posts</h3>
						<ul>
							<?php
							$recent_posts = wp_get_recent_posts( array(
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
							) );
							foreach( $recent_posts as $recent ){
								echo '<li><a href="' . get_permalink( $recent["ID"] ) . '">' .   $recent["post_title"].'</a> </li> ';
							}
							wp_reset_query();
							?>
						</ul>
					</div>
				</aside>
			</section><!-- .error-404 -->
		</main><!-- #main -->
	</div><!-- #primary -->
<?php get_footer();
