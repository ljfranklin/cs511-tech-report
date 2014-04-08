<?php
/*
Plugin Name: Remove_from_Dashboard
Description: Remove menu items from dashboard.

*/

   add_action( 'admin_menu', 'remove_menu_pages' );

   function remove_menu_pages() {
 
       remove_menu_page('edit.php');   
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
	
add_action( 'wp_before_admin_bar_render', 'remove_wp');
function remove_wp()
{
global $wp_admin_bar;
$wp_admin_bar->remove_menu('wp-logo');
 $wp_admin_bar->remove_menu('new-content');  
   $wp_admin_bar->remove_menu('updates'); 
$wp_admin_bar->remove_node( 'widgets' );

$wp_admin_bar->remove_node( 'themes' );
$wp_admin_bar->remove_node( 'customize' );
$wp_admin_bar->remove_node( 'header' );
$wp_admin_bar->remove_node( 'menus' );


}





?>