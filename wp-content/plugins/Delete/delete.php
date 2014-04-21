<?php
/*
Plugin Name: Remove_from_Dashboard
Description: Remove menu items from dashboard.

*/

	add_action( 'admin_menu', 'remove_menu_pages' );
	function remove_menu_pages() {
 
       	remove_menu_page('edit.php');
       	remove_menu_page('themes.php');
       	remove_menu_page('index.php');         
       	remove_menu_page('upload.php'); 
       	remove_menu_page('tools.php'); 
		remove_menu_page('edit.php?post_type=page'); 
		remove_menu_page('edit-comments.php'); 

		remove_menu_page('options-general.php'); 

		global $submenu;  
		unset($submenu['index.php'][10]);
		add_filter('screen_options_show_screen', '__return_false');
	}

	add_action('admin_head', 'mytheme_remove_help_tabs');
	function mytheme_remove_help_tabs() {
    	$screen = get_current_screen();
    	$screen->remove_help_tabs();
	}
	
	add_action( 'wp_before_admin_bar_render', 'remove_admin_bar_links');
	function remove_admin_bar_links() {
		global $wp_admin_bar;
		
		$wp_admin_bar->remove_menu('wp-logo');
 		$wp_admin_bar->remove_menu('new-content');  
   		$wp_admin_bar->remove_menu('updates'); 
		$wp_admin_bar->remove_node( 'widgets' );
		$wp_admin_bar->remove_node( 'themes' );
		$wp_admin_bar->remove_node( 'customize' );
		$wp_admin_bar->remove_node( 'header' );
		$wp_admin_bar->remove_node( 'menus' );
		$wp_admin_bar->remove_node( 'edit' );
		$wp_admin_bar->remove_node( 'dashboard' );
		$wp_admin_bar->remove_node( 'search' );

		//replace default admin link with papers list link
		$main_menu_data = $wp_admin_bar->get_node( 'site-name' );
		if ($main_menu_data->href !== (site_url() . '/')) {
			$main_menu_data->href = admin_url("admin.php?page=list-papers");
			$wp_admin_bar->remove_node( 'site-name' );
			$wp_admin_bar->add_menu($main_menu_data);
		}
		
	}

	add_filter("login_redirect", "redirect_to_papers_on_login", 10, 3);
	function redirect_to_papers_on_login( $redirect_to, $request, $user ) {
		//is user admin or collaborator
    	if( property_exists($user, 'roles') && is_array($user->roles)) {
        	return admin_url("admin.php?page=list-papers");
    	}
	}

	//disable update reminders
	add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) );
	add_filter('site_transient_update_plugins', create_function( '$a', "return null;" ));
	
	//remove edit links
	add_filter('edit_post_link', 'wpse_remove_edit_post_link');
	function wpse_remove_edit_post_link( $link ) {
		return '';
	}
	
	$wp_roles = new WP_Roles();
	$wp_roles->remove_role("editor");
	$wp_roles->remove_role("author");
	$wp_roles->remove_role("subscriber");
?>
