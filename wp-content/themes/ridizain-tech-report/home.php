<?php


$wp_roles = new WP_Roles();
$wp_roles->remove_role("editor");
$wp_roles->remove_role("author");
$wp_roles->remove_role("subscriber");
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme and one
 * of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query,
 * e.g., it puts together the home page when no home.php file exists.
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 *
 * @package Ridizain
 * @since Ridizain 1.0
 */

get_header(); ?>
	
	<div id="primary" class="content-area">

		<div id="content" class="site-content" role="main">
		
		<header class="entry-header">
			<h1 class="entry-title">Recent Papers</h1>
		</header>
        
        <?php 
        	$tech_report = new TechReports(); 
        	
        	$paper_id = isset($_GET['paper']) ? $_GET['paper'] : NULL;
        	if ($paper_id === NULL) {
				$tech_report->query_papers();
			} else {
				$tech_report->query_papers($paper_id);
			}
        ?>
        
		<?php
			if ( $tech_report->have_papers() ) :
				// Start the Loop.
				while ( $tech_report->have_papers() ) : $tech_report->the_paper();
					//get_template_part( 'content', get_post_format() );
					include('content.php');

				endwhile;
				// Previous/next post navigation.
				//ridizain_paging_nav();

			else :
				// If no content, include the "No posts found" template.
				get_template_part( 'content', 'none' );

			endif;
		?>

		</div><!-- #content -->
	</div><!-- #primary -->
	<!--<?php //get_sidebar( 'content' ); ?>-->
</div><!-- #main-content -->

<?php
//get_sidebar();
get_footer();
