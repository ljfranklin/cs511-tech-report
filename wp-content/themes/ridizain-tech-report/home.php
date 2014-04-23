<?php
global $tech_report;

get_header(); ?>
	
	<?php 
    	
    	$paper_id = isset($_GET['paper']) ? $_GET['paper'] : NULL;
    	$current_page = isset($_GET['pagination']) ? intval($_GET['pagination']) : 1;
    	
    	if ($paper_id === NULL) {
    		$page_args = array(
				'current_page' => $current_page,
				'per_page' => 20
			);
			$tech_report->query_recent_papers($page_args);
		} else {
			$tech_report->query_single_paper($paper_id);
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
			if ( $tech_report->have_papers() ) : ?>
			
				<div class="pagination_links">
					<?php for ($i = 1; $i <= $tech_report->get_total_pages(); $i++) : ?>
						<span class="<?php if ($current_page === $i) echo 'current_page'; ?>">
							<a href="<?php echo site_url() . '/?pagination=' . $i ?>">
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
