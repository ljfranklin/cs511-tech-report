<?php
/**
 * The Content Sidebar
 *
 *
 * @package Ridizain
 * @since Ridizain 1.0
 */

if ( ! is_active_sidebar( 'sidebar-2' ) ) {
	return;
}
?>
<?php tha_sidebars_before(); ?>
<div id="content-sidebar" class="content-sidebar widget-area" role="complementary">
	<?php tha_sidebar_top(); ?>
	<?php dynamic_sidebar( 'sidebar-2' ); ?>
	<?php tha_sidebar_bottom(); ?>
</div><!-- #content-sidebar -->
<?php tha_sidebars_after(); ?>
