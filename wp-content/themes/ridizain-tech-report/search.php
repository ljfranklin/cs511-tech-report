
<?php

get_header(); ?>
	
	<?php 
    	$tech_report = new TechReports(); 
    	
    	$search_query = get_search_query();
    	$tech_report->query_papers_by_search($search_query);
    ?>
	
	<div id="primary" class="content-area">

		<div id="content" class="site-content" role="main">
		
		<header class="page-header">
			<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'ridizain' ), get_search_query() ); ?></h1>
		</header>
        
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

