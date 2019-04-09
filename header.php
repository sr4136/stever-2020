<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package stever
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'stever' ); ?></a>
	<div class="header-wrapper">
		<header id="masthead" class="grid-container">
			<div class="site-header grid-x grid-padding-x">
				<div class="site-branding large-8 medium-8 cell">
					<?php the_custom_logo(); ?>
				</div><!-- .site-branding -->

				<nav id="social-navigation" class="social-navigation large-4 medium-4 cell">
					<?php
					wp_nav_menu( array(
						'theme_location' => 'social-icons',
						'menu_id'        => 'social-icons',
						'depth'			=> 1,
						'menu_class'		=> 'menu align-right',
					) );
					?>
				</nav><!-- #site-navigation -->

				<nav id="site-navigation" class="main-navigation large-4 medium-4 cell">
					<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e( 'Primary Menu', 'stever' ); ?></button>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'menu-1',
						'menu_id'        => 'primary-menu',
						'depth'			=> 1,
						'menu_class'		=> 'menu align-right',
					) );
					?>
				</nav><!-- #site-navigation -->
			</div>
		</header><!-- #masthead -->
	</div>

	<div id="content" class="site-content grid-container">
