<?php
/**
 * Plugin Name: Technical Reports Plugin
 * Description: A plugin to enable saving and displaying of research papers 
 */
 
class TechReports {

	private $paper_db;
	private $paper_values = NULL;

	function __construct($paper_id=NULL) {
		$this->paper_db = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
	}

	public static function tech_reports_admin_edit() {
    	include('tech_reports_admin_edit.php');
	}
	
	public static function tech_reports_admin_list() {
		include('tech_reports_admin_list.php');	
	}

	public static function tech_reports_admin_actions() {
 		add_menu_page("Research Papers", "Research Papers", "edit_posts", "list-papers", array("TechReports", "tech_reports_admin_list"));
 		add_submenu_page("list-papers", "All Papers", "All Papers", "edit_posts", "list-papers", array("TechReports", "tech_reports_admin_list"));
 		add_submenu_page("list-papers", "New Paper", "Add Paper", "edit_posts", "upload-paper", array("TechReports", "tech_reports_admin_edit"));
	}
	
	public function get_paper_for_post($post_id) {
		$paper_id = get_post_meta($post_id, 'paper_id', true);
		$paper = $this->paper_db->get_row("SELECT * FROM paper WHERE paper_id=$paper_id", ARRAY_A);
		if ($paper == NULL) {	
			return array();
		}
		$paper['file'] = $this->get_paper_url($paper);
		return $paper;
	}
	
	public function get_paper($paper_id) {
		$paper = $this->paper_db->get_row("SELECT * FROM paper WHERE paper_id=$paper_id", ARRAY_A);
		if ($paper == NULL) {	
			return array();
		}
		$paper['file'] = $this->get_paper_url($paper);
		return $paper;
	}
	
	private function get_paper_filename($paper_id, $title=NULL) {
	
		if (is_null($title)) {
			$title = $this->paper_db->get_var("SELECT title FROM paper WHERE paper_id=$paper_id");
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
    
    private function get_paper_url($paper) {
	 	$pdf_path = $this->get_paper_filename($paper['paper_id'], $paper['title']);
		$base_path = ABSPATH;
		return "../" . substr($pdf_path, strlen($base_path));
	}
	
	public function get_search_results($query_term) {
		$paper_ids = $this->paper_db->get_col("SELECT paper_id FROM paper WHERE author LIKE '%$query_term%' OR title LIKE '%$query_term%' OR abstract LIKE '%$query_term%'");
		if ($paper_ids == NULL) {
			return new WP_Query();
		}
		
		$args = array (
			'meta_query' => array(
	       		array(
	           		'key' => 'paper_id',
	           		'value' => $paper_ids,
	           		'compare' => 'IN',
	       		)
	   		)
		);
 
		$search_query = new WP_Query( $args );
		
		return $search_query;
	}
	
	public function delete_paper($paper_id) {
		global $wpdb;

		$query = "SELECT wposts.ID
			FROM ".$wpdb->posts." AS wposts
			INNER JOIN ".$wpdb->postmeta." AS wpostmeta
			ON wpostmeta.post_id = wposts.ID
			AND wpostmeta.meta_key = 'paper_id'
			AND wpostmeta.meta_value = '$paper_id'";
		
		$post_id = $wpdb->get_var($query);
		wp_delete_post($post_id, true);
		
		unlink($this->get_paper_filename($paper_id));
		$this->paper_db->delete( 'paper', array( 'paper_id' => $paper_id ));	
	}

	public function delete_multiple_papers($paper_ids) {
		global $wpdb;
	
		$id_string = "'" . implode("','", $paper_ids) . "'";
		$query = "SELECT wposts.ID
			FROM ".$wpdb->posts." AS wposts
			INNER JOIN ".$wpdb->postmeta." AS wpostmeta
			ON wpostmeta.post_id = wposts.ID
			AND wpostmeta.meta_key = 'paper_id'
			AND wpostmeta.meta_value IN ($id_string)";
		
		$post_ids = $wpdb->get_col($query);
		foreach ($post_ids as $post_id) {
			wp_delete_post($post_id, true);
		}	
		
		foreach ($paper_ids as $paper_id){
			unlink($this->get_paper_filename($paper_id));
			$this->paper_db->delete( 'paper', array( 'paper_id' => $paper_id ));	
		}
	}
	
	public function get_all_papers() {
		$query = "SELECT * FROM paper";
		return $this->paper_db->get_results($query);
	}
	
	public function add_new_paper($values) {
       
        $this->paper_db->insert( 
			'paper', 
			array( 
				'title' => $values['title'],
				'author' => $values['author'],
				'abstract' => $values['abstract']
			), 
			array( 
				'%s',
				'%s',
				'%s'
			) 
		);
		$paper_id = $this->paper_db->insert_id;
		
		$user_id = get_current_user_id();
		$new_post = array(
			'post_title' => $values['title'],
			'post_content' => '',
			'post_status' => 'publish',
			'post_date' => date('Y-m-d H:i:s'),
			'post_author' => $user_id,
			'post_type' => 'post',
			'post_category' => array(0)
		);
		$post_id = wp_insert_post($new_post);
		add_post_meta($post_id, 'paper_id', $paper_id);
		
		$this->process_file_upload($paper_id, $values['title'], $values['file']);
		
		return $post_id;
    }
    
    private function process_file_upload($paper_id, $title, $file) {
    
    	$finfo = new finfo(FILEINFO_MIME_TYPE);
    	if (false === array_search(
    	    $finfo->file($file['tmp_name']),
    	    array(
    	        'pdf' => 'application/pdf'
    	    ),
    	    true
    	)) {
        	throw new RuntimeException('Invalid file format.');
    	}
    	
		if (!move_uploaded_file(
        	$file['tmp_name'],
        	$this->get_paper_filename($paper_id, $title)
    	)) {
    	    throw new RuntimeException('Failed to move uploaded file.');
    	}
    }
    
    private function delete_old_file($paper_id, $old_title) {
    	unlink($this->get_paper_filename($paper_id, $old_title));
    }
    
    private function rename_old_file($paper_id, $old_title, $new_title) {
    	rename($this->get_paper_filename($paper_id, $old_title), $this->get_paper_filename($paper_id, $new_title));
    }
    
    public function update_paper($new_values, $old_title) {
	    global $wpdb;
        
        $paper_id = $new_values['paper_id'];
        $title = $new_values['title'];
        $this->paper_db->update( 
			'paper', 
			array( 
				'title' => $title,
				'author' => $new_values['author'],
				'abstract' => $new_values['abstract']
			), 
			array(
				'paper_id' => $paper_id
			),
			array( 
				'%s',
				'%s',
				'%s'
			),
			array(
				'%d'
			)
		);

		$query = "SELECT wposts.ID
			FROM ".$wpdb->posts." AS wposts
			INNER JOIN ".$wpdb->postmeta." AS wpostmeta
			ON wpostmeta.post_id = wposts.ID
			AND wpostmeta.meta_key = 'paper_id'
			AND wpostmeta.meta_value = '$paper_id'";
		
		$post_id = $wpdb->get_var($query);
		
		$updatedPost = array(
			'post_title' => $title,
			'ID' => $post_id
		);
		wp_update_post($updatedPost);
		
		if (empty($new_values['file']['tmp_name']) === false) {
			$this->process_file_upload($paper_id, $title, $new_values['file']);
		}
		
		if (empty($new_values['file']['tmp_name']) && $old_title !== $title) {
    		$this->rename_old_file($paper_id, $old_title, $title);
    	} else if ($old_title !== $title) {
			$this->delete_old_file($paper_id, $old_title);
		} 
		
		return $post_id;
    }
}

add_action('admin_menu', array('TechReports','tech_reports_admin_actions'));

?>
