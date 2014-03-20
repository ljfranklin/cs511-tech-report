<?php
/**
 * The Header for our theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 *
 * @package Ridizain
 * @since Ridizain 1.0
 */
?><!DOCTYPE html>
<?php tha_html_before(); ?>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<?php tha_head_top(); ?>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php tha_head_bottom(); ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php tha_body_top(); ?>
<div id="page" class="hfeed site">
    <?php tha_header_before(); ?>
	<?php if ( get_header_image() ) : ?>
	    <div id="site-header">
		    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			    <img src="<?php header_image(); ?>" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="<?php bloginfo( 'name' ); ?>">
		    </a>
	    </div>
	<?php endif; ?>
<?php tha_header_bottom(); ?>
<?php get_template_part( 'masthead' ); ?>

<div class="clearfix"></div>
<!-- Slider placeholder -->
<?php tha_header_after(); ?>
<div id="main" class="site-main">
<?php tha_content_top(); ?>
<div class="clearfix"></div>
<?php
if ( get_theme_mod( 'featured_content_location' ) == 'fullwidth' ) {
	if ( is_front_page() && ridizain_has_featured_posts() ) {
		// Include the featured content template.
		get_template_part( 'featured-content' );
	}
} ?>
<div id="main-content" class="main-content">

<?php
if ( get_theme_mod( 'featured_content_location' ) == 'default' ) {
	if ( is_front_page() && ridizain_has_featured_posts() ) {
		// Include the featured content template.
		get_template_part( 'featured-content' );
	}
} ?>