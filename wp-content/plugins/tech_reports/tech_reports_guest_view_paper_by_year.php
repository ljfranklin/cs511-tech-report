<?php
	
	$tech_report = new TechReports();
	
	$year = isset($_GET['year']) ? $_GET['year'] : $tech_report->get_most_recent_year();
	
	$tech_report->query_papers_by_year($year);
	
	$years = $tech_report->get_all_paper_years();
?>
	
	<div id="primary" class="content-area">

		<div id="content" class="site-content" role="main">
        
        <div class="year_pagination_links pagination_links">
    		<?php foreach ($years as $this_year) : ?>
    			<a href="<?php echo get_permalink() . '&year=' . $this_year; ?>">
					<?php echo '\'' . substr(strval($this_year), 2, 2); ?>
				</a>
			<?php endforeach; ?>
        </div>
        
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

