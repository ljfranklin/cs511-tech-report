<?php

get_header(); ?>
	
	<?php 
    	$tech_report = new TechReports(); 
    	
    	$paper_id = isset($_GET['paper']) ? $_GET['paper'] : NULL;
    	if ($paper_id === NULL) {
			$tech_report->query_recent_papers(20);
		} else {
			$tech_report->query_papers($paper_id);
		}
    ?>
	
	<div id="primary" class="content-area">

		<div id="content" class="site-content" role="main">
		
		<?php if ($tech_report->is_single() === false) : ?> 
		<header>
			<h1 class="entry-title">Recent Papers</h1>
		</header>
		<?php else : ?>
		<header>
			<h1 class="entry-title">Paper Details</h1>
		</header>
		<?php endif; ?>
        
		<?php
			if ( $tech_report->have_papers() ) :
				// Start the Loop.
				while ( $tech_report->have_papers() ) : $tech_report->the_paper();
					include('content.php');
				endwhile;

			else :
				// If no content, include the "No posts found" template.
				get_template_part( 'content', 'none' );
			endif;
		?>

		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->

<?php
get_footer();
