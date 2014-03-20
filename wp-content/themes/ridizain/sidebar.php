<?php
/**
 * The Sidebar containing the main widget area
 *
 *
 * @package Ridizain
 * @since Ridizain 1.0
 */
?>
<?php tha_sidebars_before(); ?>
<div id="secondary">
	
	<?php if ( has_nav_menu( 'social' ) ) : ?>
	    <nav role="navigation" class="navigation site-navigation secondary-navigation">
		    <?php wp_nav_menu(
		        array(
			        'theme_location'  => 'social',
			        'container'       => 'div',
			        'container_id'    => 'ridizain-social',
			        'depth'           => 1,
			        'fallback_cb'     => '',
		        )
	        ); ?>

	    </nav>
	<?php endif; ?>
	
	<?php if ( has_nav_menu( 'secondary' ) ) : ?>
	<nav role="navigation" class="navigation site-navigation secondary-navigation">
		<?php wp_nav_menu( array( 'theme_location' => 'secondary' ) ); ?>
	</nav>
	<?php endif; ?>
    <?php tha_sidebar_top(); ?>
	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
	<div id="primary-sidebar" class="primary-sidebar widget-area" role="complementary">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</div><!-- #primary-sidebar -->
	<?php endif; ?>
	<?php tha_sidebar_bottom(); ?>
</div><!-- #secondary -->
<?php tha_sidebars_after(); ?>
