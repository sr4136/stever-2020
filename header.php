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
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'stever'); ?></a>

		<header id="masthead" class="grid-container">
			<div id="site-branding">
				<a href="<?php echo home_url(); ?>" class="custom-logo-link" rel="home">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/logo.png" class="custom-logo" alt="Steve Rudolfi Logo">
				</a>
			</div>

			<nav id="social-navigation">
				<?php
				wp_nav_menu(array(
					'theme_location'	=> 'social-icons',
					'menu_id'			=> 'social-icons',
					'depth'				=> 1,
				));
				?>
			</nav>

			<nav id="site-navigation">
				<?php
				wp_nav_menu(array(
					'theme_location'	=> 'primary',
					'menu_id'			=> 'primary-menu',
					'depth'				=> 1,
				));
				?>
			</nav>
		</header>

		<div id="content" class="site-content grid-container">