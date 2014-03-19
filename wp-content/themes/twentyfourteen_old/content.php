<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		<?php echo getHelloWorld(); ?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php echo getPaperTitle(get_the_ID()); ?>
	</div><!-- .entry-content -->

</article><!-- #post-## -->
