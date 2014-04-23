
<?php

global $tech_report;

get_header(); ?>
	
	<?php 
    	
    	$current_page = isset($_GET['pagination']) ? intval($_GET['pagination']) : 1;
    	
    	$search_query = get_search_query();
    	$page_args = array(
    		'current_page' => $current_page,
    		'per_page' => 20
    	);
    	$tech_report->query_papers_by_search($search_query, $page_args);
    ?>
	
	<div id="primary" class="content-area">

		<div id="content" class="site-content" role="main">
		
		<header class="page-header">
			<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'ridizain' ), get_search_query() ); ?></h1>
		</header>
        
		<?php if ( $tech_report->have_papers() ) : ?>
			<div class="pagination_links">
				<?php for ($i = 1; $i <= $tech_report->get_total_pages(); $i++) : ?>
					<span class="<?php if ($current_page === $i) echo 'current_page'; ?>">
						<a href="<?php echo site_url() . '/?s=' . $search_query . '&pagination=' . $i ?>">
							<?php echo $i; ?>
						</a>
					</span>
				<?php endfor; ?>
			</div>	
			<?php
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

