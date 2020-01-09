<?php
/**
 * Custom Dashboard Widgets
 * @package SteveRudolfi
 */


/**
 * Admin Columns: Remove Unnecessary Columns
 */
 function stever_remove_admin_columns( $defaults ) {
  unset( $defaults[ 'comments' ] );
  unset( $defaults[ 'author' ] );
  unset( $defaults[ 'categories' ] );
  unset( $defaults[ 'tags' ] );
  return $defaults;
}
add_filter( 'manage_pages_columns', 'stever_remove_admin_columns' );
add_filter( 'manage_posts_columns', 'stever_remove_admin_columns' );


/**
 * Admin Menu: Custom Order
 */
 function stever_custom_menu_order( $menu_order ) {
	if ( !$menu_order ) return true;

	return array(
		'index.php', // Dashboard
		'separator1', // First separator

		'edit.php?post_type=page', // Pages
		'edit.php', // Posts
		'edit.php?post_type=stever_quotes', // Quotes
		'wp-google-maps-menu', // WP Google Maps (Plugin)
		'upload.php', // Media
		'separator2', // Second separator

		'tools.php', // Tools
		'themes.php', // Appearance
		'plugins.php', // Plugins
		'options-general.php', // Settings

		'users.php', // Users
		'link-manager.php', // Links
		'edit-comments.php', // Comments
		'separator-last', // Last separator
	);
}
add_filter( 'custom_menu_order', 'stever_custom_menu_order' );
add_filter( 'menu_order', 'stever_custom_menu_order' );


/**
 * Dashboard Widget: All Unpublished Posts
 */
function stever_dashboard_widget_unpublished() {
	$args = array(
		'post_type'			=> 'post',
		'post_status'		=> array( 'pending', 'draft', 'future', 'private' ), /* all statuses, except 'published' and 'auto-draft' */
		'posts_per_page'	=> -1,
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

/**
 * Dashboard Widget: All Pages (Hierarchical)
 */
function stever_check_for_children( $post_id=null ) {
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
					$retStr .= stever_check_for_children( get_the_id() );
				$retStr .= '</li>';
			endwhile;

			/* Add the Quotes page (archive) */
			if( $post_id == 0 ){
				$retStr .= '<li><div class="post-info-wrap"><a href="https://steverudolfi.com/wp-admin/edit.php?post_type=stever_quotes">Quotes</a><a class="linkview" href="https://steverudolfi.com/quotes/">view</a></div></li>';
			}

		$retStr .= '</ul>';
		wp_reset_postdata();
	endif;
	return $retStr;
}
function stever_dashboard_widget_all_pages( $post, $callback_args ) {
	echo( stever_check_for_children( 0 ) );
}

/**
 * Dashboard Widget: Recently Updated
 */
function stever_dashboard_widget_recently_updated() {
	$args = array(
		'post_type'			=> array( 'post', 'page' ),
		'posts_per_page'		=> 10,
		'orderby'			=> 'modified',
		'order'				=> 'DESC'
	);

	$the_query = new WP_Query( $args );
	if ( $the_query->have_posts() ) :
		echo( '<ul class="stever-dashboard-widget">' );
		while ( $the_query->have_posts() ) : $the_query->the_post();
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

/**
 * Dashboard Widget: Maps (Manual)
 */
function stever_dashboard_widget_maps() {
?>
	<ul class="stever-dashboard-widget">
		<li>
			<div class="post-info-wrap">
				<a href="/wp-admin/admin.php?page=wp-google-maps-menu&action=edit&map_id=1">Places I've Been</a>
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

/**
 * Dashboard Widgets: Add Them!
 */
function stever_add_dashboard_widgets() {
	wp_add_dashboard_widget( 'dashboard_widget_unfinished_posts', 'Unfinished Posts', 'stever_dashboard_widget_unpublished' );
	wp_add_dashboard_widget( 'dashboard_widget_all_pages', 'All Pages', 'stever_dashboard_widget_all_pages' );
	wp_add_dashboard_widget( 'dashboard_widget_recently_updated', 'Recently Updated', 'stever_dashboard_widget_recently_updated' );
	wp_add_dashboard_widget( 'dashboard_widget_maps', 'Maps', 'stever_dashboard_widget_maps' );
}
add_action( 'wp_dashboard_setup', 'stever_add_dashboard_widgets' );


/* Add "View This" button to the WP Admin bar */
function stever_view_current_in_adminbar() {
	global $wp_admin_bar;

	if( function_exists( 'get_current_screen' ) ){
		$current_screen = get_current_screen()->id;
		$status = get_post_status();

		if( is_admin() ){
			if( 'page' == $current_screen || 'post' == $current_screen ){
				if( 'private' == $status || 'publish' == $status ){
					$args = array(
						'id'    => 'stever-view',
						'title' => 'View This',
						'href'  => get_the_permalink(),
						'meta'  => array(
							'class' => 'stever-view',
							'title' => 'View This'
						)
					);
					$wp_admin_bar->add_menu( $args );
				}
			}
		}
	}
}
add_action( 'wp_before_admin_bar_render', 'stever_view_current_in_adminbar', 999 );

