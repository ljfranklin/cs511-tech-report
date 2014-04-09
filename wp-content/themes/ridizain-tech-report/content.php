<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 *
 * Adapted for use in Technical Reports child theme
 *
 * @package Ridizain
 * @since Ridizain 1.0
 */
?>
<?php tha_entry_before(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php tha_entry_top(); ?>
	<?php ridizain_post_thumbnail(); ?>

	<?php 
		$tech_report = new TechReports();
		$paper = $tech_report->get_paper_for_post(get_the_ID());	
	?>

	<header class="entry-header">
		<?php if ( is_single() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			else :
				the_title( '<h1 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h1>' );
			endif;
		?>
		
		<table class="paper_main_data">
			<tbody>
				<tr>
					<td>Author:</td>
					<td>
						<?php 
							function getFullName($author) {
								$full_name = $author['first_name'];
								if (strlen($author['middle_name']) > 0) {
									$full_name .= ' ' . $author['middle_name'];
								}
								$full_name .= ' ' . $author['last_name'];
								return $full_name;
							}
							$full_names = array_map("getFullName", $paper['authors']);
							echo implode(", ", $full_names); 
						?>
					</td>
				</tr>
				<tr>
					<td>Publication Year:</td>
					<td><?php echo $paper['publication_year']; ?></td>
				</tr>
				<tr>
					<td>Type:</td>
					<td><?php echo $paper['type']; ?></td>
				</tr>
				<tr>
					<td>Download:</td>
					<td><a href="<?php echo $paper['file']; ?>" target="_blank">PDF</a></td>
				</tr>	
			</tbody>
		</table>
		
	</header><!-- .entry-header -->
	
	

	<?php if ( is_search() || is_archive() || is_home() ) : ?>
	<div class="entry-summary">
		<?php the_excerpt(); ?>
		<p class="read-more button"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php _e( 'Read More &raquo;', 'ridizain' ); ?></a></p>
	</div><!-- .entry-summary -->
	<?php else : ?>
	<div class="entry-content">
		<div>Abstract:</div>
		<p class="paper_abstract">
			<?php echo $paper['abstract']; ?>
		</p>
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
