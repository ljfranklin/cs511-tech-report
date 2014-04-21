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
<article id="post-<?php echo $tech_report->the_ID(); ?>" <?php post_class(); ?>>
<?php tha_entry_top(); ?>

	<?php
        $get_full_names = function($authors) {
			$to_full_names = function($author) {
				return $author['full_name'];
			};

			return implode(', ', array_map($to_full_names, $authors));				
		};
        
        $get_keyword_links = function($keywords) {
        	$keywords = explode(',', $keywords);
        	
        	$to_link = function($keyword) {
        		return '<a href="' . site_url() . '/?s=' . $keyword . '">' . $keyword . '</a>';
        	};
        	
        	return implode(', ', array_map($to_link, $keywords));
        };
	?>
	
	<?php if ($tech_report->is_single()) : ?>
	<div class="paper_display">
	<?php else : ?>
	<div class="paper_display paper_expand">
	<?php endif; ?>
	
		<div class="paper_title">
			<span class="paper_title_text">
				<?php echo $tech_report->get_paper_field('title'); ?>
			</span>
			
			<span> - </span>
			<span class="paper_identifier">
				<?php echo $tech_report->get_paper_field('identifier'); ?>
			</span>
			
			<?php if ($tech_report->is_single() === false) : ?>
			<span class="expand_icon genericon genericon-expand"></span>
			<span class="collapse_icon genericon genericon-collapse"></span>
			<?php endif; ?>
		</div>
		
		<?php if ($tech_report->is_single()) : ?>
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
								$authors = $tech_report->get_paper_field('authors');
    							echo $get_full_names($authors);
							?>
						</td>
					</tr>
					<tr>
						<th>Publication Year:</th>
						<td><?php echo $tech_report->get_paper_field('publication_year'); ?></td>
					</tr>
					<tr>
						<th>Type:</th>
						<?php $paper_type = $tech_report->get_paper_field('type'); ?>
						<td><?php echo $paper_type; ?></td>
					</tr>
					<?php if ($paper_type === 'journal') : ?>
					<tr>
						<th>Journal:</th>
						<td><?php echo $tech_report->get_paper_field('published_at'); ?></td>
					</tr>
					<?php elseif ($paper_type === 'conference') : ?>
					<tr>
						<th>Conference:</th>
						<td><?php echo $tech_report->get_paper_field('published_at'); ?></td>
					</tr>
					<?php endif; ?>
					<tr>
						<th>Download:</th>
						<td><a href="<?php echo $tech_report->get_paper_field('file'); ?>" target="_blank">PDF</a></td>
					</tr>
					<tr>
						<th>Keywords:</th>
						<td>
							<?php 
								$keywords = $tech_report->get_paper_field('keywords');
    							echo $get_keyword_links($keywords);
							?>
						</td>
					</tr>
				</tbody>
			</table>
			
			<?php if ($tech_report->is_single()) : ?>
			<div class="paper_citation">
				<label>Citation:</label>
	            <p>
	            	<?php echo $tech_report->get_paper_field('citation'); ?>
	            </p>	
            </div>
			<?php endif; ?>
			<div class="paper_abstract">
				<label>Abstract:</label>
				<p>
					<?php echo $tech_report->get_paper_field('abstract'); ?>
				</p>
			</div>
			
			<?php if ($tech_report->is_single() === false) : ?>
			<p class="read-more button">
				<a href="<?php echo $tech_report->get_permalink(); ?>"><?php _e( 'View Details &raquo;', 'ridizain' ); ?></a>
			</p>
			<?php endif; ?>
			
		</div>
	</div>

	<?php tha_entry_bottom(); ?>
</article><!-- #post-## -->
<?php tha_entry_after(); ?>
