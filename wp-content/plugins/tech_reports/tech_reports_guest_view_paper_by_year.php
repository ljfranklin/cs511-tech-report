<?php
	global $tech_report;
	
	$years = $tech_report->get_paper_repo()->get_all_paper_years();
	
	if (isset($_GET['year'])) {
		$year =  $_GET['year'];
	} else if (count($years) > 0) {
		$year = $years[0];
	} else {
		$year = NULL;
	} 
	
	$tech_report->query_papers_by_year($year);
?>
	
	<div id="primary" class="content-area">

		<div id="content" class="site-content" role="main">
		
		<?php $split_years = array_chunk($years, 10); ?>
		<?php foreach ($split_years as $year_block) : ?>
			<div class="year_pagination_links pagination_links">
			<?php foreach ($year_block as $this_year) : ?>
				<span class="<?php if ($year === $this_year) echo 'current_page'; ?>">
					<a href="<?php echo get_permalink() . '&year=' . $this_year; ?>">
						<?php echo $this_year; ?>
					</a>
				</span>
			<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
        
        <h1 class="year_header">
			<?php echo $year; ?>
		</h1>
        
		<?php if ( $tech_report->have_papers() ) : ?>
			<?php 
				// Start the Loop.
				while ( $tech_report->have_papers() ) : $tech_report->the_paper(); 
					include(get_stylesheet_directory() . '/content.php');
				endwhile;
			?>
		<?php 
			else :
				// If no content, include the "No posts found" template.
				get_template_part( 'content', 'none' );
			endif;
		?>

		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->

