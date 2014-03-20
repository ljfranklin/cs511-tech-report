<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 *
 *
 * @package Ridizain
 * @since Ridizain 1.0
 */
?>
<?php tha_entry_before(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php tha_entry_top(); ?>
	<?php ridizain_post_thumbnail(); ?>

	<header class="entry-header">
		<?php if ( is_single() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			else :
				the_title( '<h1 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h1>' );
			endif;
		?>

		<div class="entry-meta">
			<?php if ( in_array( 'category', get_object_taxonomies( get_post_type() ) ) && ridizain_categorized_blog() ) : ?>
		
			<span class="cat-links"><?php echo get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'ridizain' ) ); ?></span>
		<?php
			endif;
				if ( 'post' == get_post_type() )
					ridizain_posted_on();

				if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) :
			?>
			<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'ridizain' ), __( '1 Comment', 'ridizain' ), __( '% Comments', 'ridizain' ) ); ?></span>
			<?php
				endif;

				edit_post_link( __( 'Edit', 'ridizain' ), '<span class="edit-link">', '</span>' );
			?>
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->

	<?php if ( is_search() || is_archive() ) : ?>
	<div class="entry-summary">
		<?php the_excerpt(); ?>
		<p class="read-more button"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php _e( 'Read More &raquo;', 'ridizain' ); ?></a></p>
	</div><!-- .entry-summary -->
	<?php else : ?>
	<div class="entry-content">
		<?php
			the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'ridizain' ) );
			wp_link_pages( array(
				'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'ridizain' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
			) );
		?>
	</div><!-- .entry-content -->
	<?php endif; ?>

	<?php the_tags( '<footer class="entry-meta"><span class="tag-links">', '', '</span></footer>' ); ?>
	<?php tha_entry_bottom(); ?>
</article><!-- #post-## -->
<?php tha_entry_after(); ?>
