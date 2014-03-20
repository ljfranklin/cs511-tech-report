<?php
/**
 * The template for displaying Search Results pages
 *
 *
 * @package Ridizain
 * @since Ridizain 1.0
 */
 
 
function getPaperSearchResults($queryTerm) {
	$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");

	$paperIds = $paperDb->get_col("SELECT paper_id FROM paper WHERE author LIKE '%$queryTerm%' OR title LIKE '%$queryTerm%' OR abstract LIKE '%$queryTerm%'");
	if ($paperIds == NULL) {
		return new WP_Query();
	}
	
	$args = array (
		'meta_query' => array(
       		array(
           		'key' => 'paper_id',
           		'value' => $paperIds,
           		'compare' => 'IN',
       		)
   		)
	);
 
	$search_query = new WP_Query( $args );
		
	return $search_query;
}

get_header(); ?>

	<section id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<?php $search_results = getPaperSearchResults(get_search_query()); ?>

			<?php if ( $search_results->have_posts() ) : ?>

			<header class="page-header">
				<h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'ridizain' ), get_search_query() ); ?></h1>
			</header><!-- .page-header -->

				<?php
					// Start the Loop.
					while ( $search_results->have_posts() ) : $search_results->the_post();

						/*
						 * Include the post format-specific template for the content. If you want to
						 * use this in a child theme, then include a file called called content-___.php
						 * (where ___ is the post format) and that will be used instead.
						 */
						get_template_part( 'content', get_post_format() );

					endwhile;
					// Previous/next post navigation.
					ridizain_paging_nav();

				else :
					// If no content, include the "No posts found" template.
					get_template_part( 'content', 'none' );

				endif;
			?>

		</div><!-- #content -->
	</section><!-- #primary -->

<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
