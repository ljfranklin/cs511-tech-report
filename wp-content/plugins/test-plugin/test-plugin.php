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
 		add_menu_page("Upload Paper", "Upload Paper", 1, "Upload Paper", "test_admin");
 		add_menu_page("List Papers", "List Papers", 1, "ListPapers", "test_admin_list");
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
 
	add_action('admin_menu', 'test_admin_actions');
?>
