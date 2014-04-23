<?php
	global $tech_report;
	
	$initials = $tech_report->get_paper_repo()->get_author_initials();
	
	if (isset($_GET['letter'])) {
		$first_letter = $_GET['letter'];
	} else if (empty($initials) === false) {
		$first_letter = $initials[0];
	} else {
		$first_letter = 'A';
	}
	
	$tech_report->query_papers_by_author($first_letter);
?>
	
	<div id="primary" class="content-area">

		<div id="content" class="site-content" role="main">
        
        <div class="author_pagination_links pagination_links">
    		<?php foreach (range('A', 'Z') as $letter) : ?>
    			<span class="<?php if ($first_letter === $letter) echo 'current_page'; ?>">
    			<?php if (in_array($letter, $initials)) : ?>
				<a href="<?php echo get_permalink() . '&letter=' . $letter; ?>">
					<?php echo $letter; ?>
				</a>
				<?php else : ?>
				<span>
					<?php echo $letter; ?>
				</span>
				<?php endif; ?>
				</span>
			<?php endforeach; ?>
        </div>
        
        <h1 class="author_initial">
			<?php echo $first_letter; ?>
		</h1>
        
		<?php if ( $tech_report->have_authors() ) : ?>
			
			<?php 
				// Start the Loop.
				$last_initial = NULL;
				while ( $tech_report->have_authors() ) : $tech_report->the_author(); 
			?>
				
				<div class="paper_display paper_expand">
					<div class="paper_title">
						<span class="paper_title_text">
							<?php echo $tech_report->get_author_field('full_name'); ?>
						</span>
		
						<span>
							(<?php echo count($tech_report->get_author_field('papers')); ?>)
						</span>
		
						<span class="expand_icon genericon genericon-expand"></span>
						<span class="collapse_icon genericon genericon-collapse"></span>
					</div>
	
					<div class="paper_body hide">
						<?php
						while ( $tech_report->have_author_papers() ) : $tech_report->the_author_paper();
							include(get_stylesheet_directory() . '/content.php');
						endwhile;
						?>
					</div>
				</div>
			
			<?php endwhile; ?>
			
		<?php 
			else :
				// If no content, include the "No posts found" template.
				get_template_part( 'content', 'none' );
			endif;
		?>

		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->

