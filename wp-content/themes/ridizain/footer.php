<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 *
 * @package Ridizain
 * @since Ridizain 1.0
 */
?>
<?php tha_content_bottom(); ?>
</div><!-- #main -->
</div><!-- #page -->
<?php tha_content_after(); ?>
    <?php tha_footer_before(); ?>
		<footer id="colophon" class="site-footer" role="contentinfo">
        <?php tha_footer_top(); ?>
			<?php get_sidebar( 'footer' ); ?>

			<div class="site-info">
				<?php do_action( 'ridizain_credits' ); ?>
				<a href="<?php echo esc_url( __( 'http://wordpress.org/', 'ridizain' ) ); ?>"><?php printf( __( 'Proudly powered by %s', 'ridizain' ), 'WordPress' ); ?></a>
				<span class="sep"> || </span>
				<a href="<?php echo esc_url( __( 'http://ridizain.com/', 'ridizain' ) ); ?>"><?php printf( __( 'Themed by %s', 'ridizain' ), 'Ridizain' ); ?></a>
			</div><!-- .site-info -->
		<?php tha_footer_bottom(); ?>
		</footer><!-- #colophon -->
	<?php tha_footer_after(); ?>
    <?php tha_body_bottom(); ?>
	<?php wp_footer(); ?>
</body>
</html>