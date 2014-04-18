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
		
		$get_full_names = function($authors) use ($tech_report) {
			$full_names = array();
			foreach ($authors as $author) {
				array_push($full_names, $author['full_name']);
			}
			return $full_names;						
		};
                $generate_citation = function() use ($paper) {
                       $citation = "";
                       $authors = $paper['authors'];
                       $num_authors = count($authors);

                       $citation .= $authors[0]['first_name'] . " " . $authors[0]['last_name'];
                       
                       if ($num_authors > 1) {
                           for ($i=1;$i<$num_authors-1;$i++) {
                               $author = $authors[$i];
                               $citation .= ", " . $author['first_name'] . " " . $author['last_name'];
                           }
                           $citation .= " and " . $authors[$num_authors-1]['first_name'] . " " . $authors[$num_authors-1]['last_name'];                       
                       }
                       $citation .= ", \" " . $paper['title'] . "\", ";
                       $citation .=  $paper['publication_year'] . ".";
                       return $citation;
                };
	?>

	<header class="entry-header">
	
		<?php if (is_single()) : ?>
		<div class="paper_display">
		<?php else : ?>
		<div class="paper_display paper_expand">
		<?php endif; ?>
		
			<div class="paper_title">
				<?php the_title( '<span class="entry-title">', '</span>' ); ?>
				
				<?php if (is_single() === false) : ?>
				<span class="expand_icon genericon genericon-expand"></span>
				<span class="collapse_icon genericon genericon-collapse"></span>
				<?php endif; ?>
			</div>
			
			<?php if (is_single()) : ?>
			<div class="paper_body">	
			<?php else : ?>
			<div class="paper_body hide">
			<?php endif; ?>
			
				<table class="paper_main_data">
					<tbody>
						<tr>
							<th>Author:</th>
							<td>
								<?php 
									$full_names = $get_full_names($paper['authors']);
									echo implode(", ", $full_names); 
								?>
							</td>
						</tr>
						<tr>
							<th>Publication Year:</th>
							<td><?php echo $paper['publication_year']; ?></td>
						</tr>
						<tr>
							<th>Type:</th>
							<td><?php echo $paper['type']; ?></td>
						</tr>
						<tr>
							<th>Download:</th>
							<td><a href="<?php echo $paper['file']; ?>" target="_blank">PDF</a></td>
						</tr>	
					</tbody>
				</table>
				<?php if (is_single()) : ?>
				<div class="paper_citation">
					<label>Citation:</label>
		            <p>
		            	<?php echo $generate_citation(); ?>
		            </p>	
                </div>
				<?php endif; ?>
				<div class="paper_abstract">
					<label>Abstract:</label>
					<p>
						<?php echo $paper['abstract']; ?>
					</p>
				</div>
				<?php if (is_single() === false) : ?>
				<p class="read-more button"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php _e( 'View Details &raquo;', 'ridizain' ); ?></a></p>
				<?php endif; ?>
			</div>
		</div>
		
	</header><!-- .entry-header -->

	<?php tha_entry_bottom(); ?>
</article><!-- #post-## -->
<?php tha_entry_after(); ?>
