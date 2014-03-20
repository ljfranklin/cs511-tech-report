<?php
/**
 * Plugin Name: Test Plugin
 * Description: Just a test
 */
	function getPaperTitle($paperId) {
		$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");

		$paperTitle = $paperDb->get_var("SELECT title FROM paper WHERE paper_id=$paperId");
		if ($paperTitle == NULL) {	
			return "Can't find paper with id=$paperId";
		}

		return $paperTitle;
	}

	function test_admin() {
    	include('test_admin.php');
	}
	
	function test_admin_list() {
		include('test_admin_list.php');	
	}

	function test_admin_actions() {
 		add_menu_page("Research Papers", "Research Papers", 1, "list-papers", "test_admin_list");
 		add_submenu_page("list-papers", "All Papers", "All Papers", 1, "list-papers", "test_admin_list");
 		add_submenu_page("list-papers", "New Paper", "Add Paper", 1, "upload-paper", "test_admin");
	}
	
	function test_get_metadata($postId) {
		$paperId = get_post_meta($postId, 'paper_id', true);
		echo getPaperTitle($paperId);
	}
	
	function test_paper_abstract($postId) {
		$paperId = get_post_meta($postId, 'paper_id', true);
		
		$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");

		$paperAbstract = $paperDb->get_var("SELECT abstract FROM paper WHERE paper_id=$paperId");
		if ($paperAbstract == NULL) {	
			return "Can't find paper with id=$paperId";
		}

		return $paperAbstract;
	}
	
	function test_paper_author($postId) {
		$paperId = get_post_meta($postId, 'paper_id', true);
		$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");

		$paperAuthor = $paperDb->get_var("SELECT author FROM paper WHERE paper_id=$paperId");
		if ($paperAuthor == NULL) {	
			return "Can't find paper with id=$paperId";
		}

		return $paperAuthor;
	}
	
	function get_paper_filename($paper_id, $title=NULL) {
	
		if (is_null($title)) {
			$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
			$title = $paperDb->get_var("SELECT title FROM paper WHERE paper_id=$paper_id");
		}
	
    	$plugin_dir = plugin_dir_path( __FILE__ );
    	$filename = preg_replace("/[^a-zA-Z0-9]+/", "", $title);
    	$maxlength = 15;
    	$filename = substr($filename, 0, $maxlength);
    	$filename .= "-" . strval($paper_id);
    	
    	return sprintf('%suploads/%s.pdf',
        		$plugin_dir,
        	    $filename
        	);
    }
    
     function get_paper_pdf($postId) {
     	$paperId = get_post_meta($postId, 'paper_id', true);
		$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
		$title = $paperDb->get_var("SELECT title FROM paper WHERE paper_id=$paperId");
     
	 	$pdf_path = get_paper_filename($paperId, $title);
		$base_path = ABSPATH;
		return "../" . substr($pdf_path, strlen($base_path));
	}
	
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
 
	add_action('admin_menu', 'test_admin_actions');
?>
