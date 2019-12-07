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
$content_length_str = ' data-contentlength="' . $content_length . '"';
$content_size = null;
$slide_time = 30000;

if( $content_length > 350 ){
	$slide_time = ( ( .046 * ( $content_length -350 ) ) + 30 ) * 1000;
}

$slide_time_str = ' data-slidetime="' . $slide_time . '"';

if( $content_length >= 450 ){
	$content_size = 'clLarge';
}
?>

<article id="post-<?php the_ID(); ?>" <?php echo $slide_time_str; ?> <?php echo $content_length_str; ?><?php post_class( $content_size ); ?>>
	<div class="entry-content">
		<?php the_content(); ?>
	</div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->
