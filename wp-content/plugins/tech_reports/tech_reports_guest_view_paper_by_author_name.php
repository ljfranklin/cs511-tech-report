<?php
	
	$tech_report = new TechReports();
	$tech_report->query_papers_by_author();
	
	$initials = $tech_report->get_author_initials();
?>
	
	<div id="primary" class="content-area">

		<div id="content" class="site-content" role="main">
        
        <div class="author_pagination_links pagination_links">
    		<?php foreach (range('A', 'Z') as $letter) : ?>
    			<span>
    			<?php if (in_array($letter, $initials)) : ?>
				<a href="#initial-<?php echo $letter; ?>">
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
        
		<?php if ( $tech_report->have_authors() ) : ?>
			
			<?php 
				// Start the Loop.
				$last_initial = NULL;
				while ( $tech_report->have_authors() ) : $tech_report->the_author(); 
			?>
				<?php $initial = strtoupper(substr($tech_report->get_author_field('last_name'), 0, 1)); ?>
					
				<?php if ($last_initial !== $initial) : ?>
					<h1 class="author_initial">
						<a name="initial-<?php echo $initial; ?>">
							<?php echo $initial; ?>
						</a>
					</h1>
					
					<?php $last_initial = $initial; ?>
				<?php endif; ?>
				
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

