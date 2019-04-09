<?php
/**
 * Custom Dashboard Widgets
 * @package SteveRudolfi
 */
 
// This function does the actual outputting of the content of the widget
function dashboard_widget_unpublished() {
	$args = array(
		'post_type'			=> 'post', /* for my purposes, I only want posts */
		'post_status'		=> array( 'pending', 'draft', 'future', 'private' ), /* all statuses, except 'published' and 'auto-draft' */
		'posts_per_page'		=> -1,
		'orderby'			=> 'date',
		'order'				=> 'ASC'
	);

	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) :
		echo( '<ul>' );
		while ( $the_query->have_posts() ) : $the_query->the_post();
		// Outputs the Post title (linked to the edit page,
		// the post status,
		// and an html entity icon (&curren;) linking to view the page
		?>
			<li>
				<a href="<?php echo( get_edit_post_link() ); ?>"><?php echo( get_the_title() ); ?></a>
				<small>(<?php echo( get_post_status() ); ?>)</small>
				<a class="linkview" href="<?php echo( get_permalink() ); ?>"> &curren;</a>
			</li>
		<?php
		endwhile;
		echo( '</ul>' );
		wp_reset_postdata();
	else :
		echo( '<p>No unfinished posts.</p>' );
	endif;
}

// This function does the actual outputting of the content of the widget
$evenOdd = 'odd';
function switchEvenOdd() {
	global $evenOdd;
	$evenOdd = ( $evenOdd == 'odd' ? 'even' : 'odd' );
}
function check_for_children( $post_id=null ) {
	global $evenOdd;
	$retStr = '';
	$args = array(
		'post_type'			=> 'page',
		'posts_per_page'		=> -1,
		'orderby'			=> 'menu_order',
		'order'				=> 'ASC',
		'post_parent'		=> $post_id
	);

	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) :
		$retStr .= '<ul>';
			while ( $the_query->have_posts() ) : $the_query->the_post();
				$retStr .= '<li class="count-' . $evenOdd . '">';
					$retStr .= '<a href="' . get_edit_post_link() . '">' . get_the_title() . '</a>';
					$retStr .= '<a class="linkview" href="' . get_permalink() . '"> &curren;</a>';
					switchEvenOdd();
					$retStr .= check_for_children( get_the_id() );
				$retStr .= '</li>';
			endwhile;
		$retStr .= '</ul>';
		wp_reset_postdata();
	endif;
	return $retStr;
}
function dashboard_widget_all_pages( $post, $callback_args ) {
	echo( check_for_children( 0 ) );
}

// This function does the actual outputting of the content of the widget
function dashboard_widget_recently_updated() {
	// set the args
	$args = array(
		'post_type'			=> array( 'post', 'page' ),
		'posts_per_page'		=> 10,
		'orderby'			=> 'modified',
		'order'				=> 'DESC'
	);

	// Standard query and loop
	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) :
		echo( '<ul>' );
		while ( $the_query->have_posts() ) : $the_query->the_post();
		// Outputs the Post title (linked to the edit page,
		// the post status,
		// and an html entity icon (&curren;) linking to view the page
		?>
			<li>
				<a href="<?php echo( get_edit_post_link() ); ?>"><?php echo( get_the_title() ); ?></a>
				<a class="linkview" href="<?php echo( get_permalink() ); ?>"> &curren;</a>
			</li>
		<?php
		endwhile;
		echo( '</ul>' );
		wp_reset_postdata();
	else :
		echo( '<p>No unfinished posts.</p>' );
	endif;
}


// Function used in the action hook
function add_dashboard_widgets() {
	wp_add_dashboard_widget( 'dashboard_widget_unfinished_posts', 'Unfinished Posts', 'dashboard_widget_unpublished' );
	wp_add_dashboard_widget( 'dashboard_widget_all_pages', 'All Pages', 'dashboard_widget_all_pages' );
	wp_add_dashboard_widget( 'dashboard_widget_recently_updated', 'Recently Updated', 'dashboard_widget_recently_updated' );
}

// Register the new dashboard widget with the 'wp_dashboard_setup' action
add_action('wp_dashboard_setup', 'add_dashboard_widgets' );



/* Adding Columns to Admin Post listing - Meta Info */

function stever_add_post_columns($columns) {
    return array_merge( $columns, array( 
		'portfolio' => __( 'Portfolio' ),
         )
	);
}
add_filter('manage_posts_columns' , 'stever_add_post_columns');

function stever_post_column_content( $column, $post_id ) {
    switch ( $column ) {

	case 'portfolio' :
		if( get_post_meta( $post_id , '_is_portfolio' , true ) == true ){
			echo( 'yes' );
		}
	    break;
    }
}

add_action( 'manage_posts_custom_column' , 'stever_post_column_content', 10, 2 );
