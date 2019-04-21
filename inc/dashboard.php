<?php
/**
 * Custom Dashboard Widgets
 * @package SteveRudolfi
 */
 
/* All Unpublished Posts */
function dashboard_widget_unpublished() {
	$args = array(
		'post_type'			=> 'post',
		'post_status'		=> array( 'pending', 'draft', 'future', 'private' ), /* all statuses, except 'published' and 'auto-draft' */
		'posts_per_page'		=> -1,
		'orderby'			=> 'date',
		'order'				=> 'ASC'
	);

	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) :
		echo( '<ul class="stever-dashboard-widget">' );
		while ( $the_query->have_posts() ) : $the_query->the_post();
		?>
			<li>
				<div class="post-info-wrap">
					<a href="<?php echo( get_edit_post_link() ); ?>"><?php echo( get_the_title() ); ?></a>
					<small class="status"><?php echo( get_post_status() ); ?></small>
					<a class="linkview" href="<?php echo( get_permalink() ); ?>">view</a>
				</div>
			</li>
		<?php
		endwhile;
		echo( '</ul>' );
		wp_reset_postdata();
	else :
		echo( '<p>No unfinished posts.</p>' );
	endif;
}

/* All Pages */
function check_for_children( $post_id=null ) {
	$retStr = '';
	$ulClass = '';
	
	if( $post_id == 0 ){
		$ulClass = ' class="stever-dashboard-widget"';
	}
	
	$args = array(
		'post_type'			=> 'page',
		'posts_per_page'		=> -1,
		'orderby'			=> 'menu_order',
		'order'				=> 'ASC',
		'post_parent'		=> $post_id
	);

	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) :
		$retStr .= '<ul' . $ulClass . '>';
			while ( $the_query->have_posts() ) : $the_query->the_post();
				$retStr .= '<li>';
					$retStr .= '<div class="post-info-wrap">';
						$retStr .= '<a href="' . get_edit_post_link() . '">' . get_the_title() . '</a>';
						$retStr .= '<a class="linkview" href="' . get_permalink() . '">view</a>';
					$retStr .= '</div>';
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

/* Recently Updated */
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
		echo( '<ul class="stever-dashboard-widget">' );
		while ( $the_query->have_posts() ) : $the_query->the_post();
		// Outputs the Post title (linked to the edit page,
		// the post status,
		// and an html entity icon (&curren;) linking to view the page
		?>
			<li>
				<div class="post-info-wrap">
					<a href="<?php echo( get_edit_post_link() ); ?>"><?php echo( get_the_title() ); ?></a>
					<a class="linkview" href="<?php echo( get_permalink() ); ?>">view</a>
				</div>
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
function dashboard_widget_maps() {
?>
	<ul class="stever-dashboard-widget">
		<li>
			<div class="post-info-wrap">
				<a href="/wp-admin/admin.php?page=wp-google-maps-menu&action=edit&map_id=1">Places I've Benn</a>
				<a class="linkview" href="/about/places-ive-been/">view</a>
			</div>
		</li>
		<li>
			<div class="post-info-wrap">
				<a href="/wp-admin/admin.php?page=wp-google-maps-menu&action=edit&map_id=2">Places I'd Like to Go</a>
				<a class="linkview" href="/about/places-ive-been/places-id-like-to-go/">view</a>
			</div>
		</li>
	</ul>
<?php
}

// Function used in the action hook
function add_dashboard_widgets() {
	wp_add_dashboard_widget( 'dashboard_widget_unfinished_posts', 'Unfinished Posts', 'dashboard_widget_unpublished' );
	wp_add_dashboard_widget( 'dashboard_widget_all_pages', 'All Pages', 'dashboard_widget_all_pages' );
	wp_add_dashboard_widget( 'dashboard_widget_recently_updated', 'Recently Updated', 'dashboard_widget_recently_updated' );
	wp_add_dashboard_widget( 'dashboard_widget_maps', 'Maps', 'dashboard_widget_maps' );
}

// Register the new dashboard widget with the 'wp_dashboard_setup' action
add_action('wp_dashboard_setup', 'add_dashboard_widgets' );
