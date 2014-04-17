<?php
/**
 * Plugin Name: Technical Reports Plugin
 * Description: A plugin to enable saving and displaying of research papers 
 */
 
class TechReports {

	private $paper_db;
	private $paper_values = NULL;

	// Xiaoran
	private $post_db;

	function __construct($paper_id=NULL) {
		$this->paper_db = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");

		// Xiaoran
		$this->post_db = new wpdb("wordpress", "wp1234", "wordpress", "localhost");
	}
	
	public static function plugin_setup() {
		self::create_plugin_table();
		self::create_upload_directory();
		self::activate_theme();
		//zongmin
		add_shortcode( 'List_Paper_By_Author_Name', array('TechReports', 'tech_reports_guest_view_paper_by_author_name') );

		add_shortcode( 'List_Paper_By_Year', array('TechReports', 'tech_reports_guest_view_paper_by_year') );

		// Xiaoran
		add_shortcode( 'List_Paper_By_Type', array('TechReports', 'tech_reports_guest_view_paper_by_type') );
		//$pages = get_pages();
		//foreach ($pages as $page) wp_delete_post($page,true);

		$page['post_type']    = 'page';
		$page['post_content'] = '\[List_Paper_By_Author_Name\]';
		$page['post_parent']  = 0;
		$page['post_status']  = 'publish';
		$page['post_title']   = 'List Papers By Author Name';
		$pageid=wp_insert_post ($page);

		$page1['post_type']    = 'page';
		$page1['post_content'] = '\[List_Paper_By_Year\]';
		$page1['post_parent']  = 0;
		$page1['post_status']  = 'publish';
		$page1['post_title']   = 'List Papers By Year';
		$pageid1=wp_insert_post ($page1);

	}

	private static function create_plugin_table() {
		$paper_db = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
		if($paper_db->get_var("SHOW TABLES LIKE 'paper'") !== 'paper') {
			$sql = "CREATE TABLE if not exists paper (
				paper_id INT NOT NULL AUTO_INCREMENT, 
				title TEXT NOT NULL,
				abstract TEXT NOT NULL,
				publication_year YEAR NOT NULL,
				type VARCHAR(40) NOT NULL,
				PRIMARY KEY (paper_id)
				);";
			$paper_db->query($sql);
		}
		if($paper_db->get_var("SHOW TABLES LIKE 'author'") !== 'author') {
			$sql = "CREATE TABLE author (
				author_id INT NOT NULL AUTO_INCREMENT,
				first_name TEXT NOT NULL, 
				middle_name TEXT NULL, 
				last_name TEXT NOT NULL,
				suffix VARCHAR(10) NULL,
				PRIMARY KEY (author_id)
				);";
			$paper_db->query($sql);
		}
		if($paper_db->get_var("SHOW TABLES LIKE 'paperAuthorAssoc'") !== 'paperAuthorAssoc') {
			$sql = "CREATE TABLE if not exists paperAuthorAssoc (
				author_id INT NOT NULL,
				paper_id INT NOT NULL,
				PRIMARY KEY (author_id, paper_id),
				FOREIGN KEY (author_id) REFERENCES author(author_id),
				FOREIGN KEY (paper_id) REFERENCES paper(paper_id)
				);";
			$paper_db->query($sql);
		}
	}
	
	private static function create_upload_directory() {
		$plugin_dir = plugin_dir_path( __FILE__ );
		$upload_path = $plugin_dir . "uploads";
		if (!file_exists($upload_path)) {
    		mkdir($upload_path, 0775);
		}
	}
	
	private static function activate_theme() {
		switch_theme('ridizain-tech-report');
	}

	public static function tech_reports_admin_edit() {
    	include('tech_reports_admin_edit.php');
	}
	
	public static function tech_reports_admin_list() {
		include('tech_reports_admin_list.php');	
	}

	//zongmin
	public static function tech_reports_guest_view_paper_by_author_name () {
		include('tech_reports_guest_view_paper_by_author_name.php');	
	}

	// Xiaoran
	public static function tech_reports_guest_view_paper_by_type() {
		include('tech_reports_guest_view_paper_by_type.php');	
	}

	public static function tech_reports_guest_view_paper_by_year() {
		include('tech_reports_guest_view_paper_by_year.php');	
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
		
		$authors_query = "SELECT author.* FROM paperAuthorAssoc INNER JOIN author 
			ON paperAuthorAssoc.paper_id=$paper_id AND paperAuthorAssoc.author_id=author.author_id";
		$paper['authors'] = $this->paper_db->get_results($authors_query, ARRAY_A);
		if (is_null($paper['authors'])) {
			$paper['authors'] = array();
		}
		
		foreach ($paper['authors'] as $key => $author) {
			$paper['authors'][$key]['full_name'] = $this->get_author_fullname($author);
		}
		
		return $paper;
	}
	
	public function get_paper($paper_id) {
		$paper = $this->paper_db->get_row("SELECT * FROM paper WHERE paper_id=$paper_id", ARRAY_A);
		if ($paper == NULL) {	
			return array();
		}
		$paper['file'] = $this->get_paper_url($paper);
		
		$authors_query = "SELECT author.* FROM paperAuthorAssoc INNER JOIN author 
			ON paperAuthorAssoc.paper_id=$paper_id AND paperAuthorAssoc.author_id=author.author_id";
		$paper['authors'] = $this->paper_db->get_results($authors_query, ARRAY_A);
		if (is_null($paper['authors'])) {
			$paper['authors'] = array();
		}
		
		foreach ($paper['authors'] as $key => $author) {
			$paper['authors'][$key]['full_name'] = $this->get_author_fullname($author);
		}
		
		return $paper;
	}
	
	private function get_author_fullname($author) {
		$full_name = $author['first_name'];
		if (strlen($author['middle_name']) > 0) {
			$full_name .= ' ' . $author['middle_name'];
		}
		$full_name .= ' ' . $author['last_name'];
		if (strlen($author['suffix']) > 0) {
			$full_name .= ' ' . strtoupper($author['suffix']);
		}
		return $full_name;
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
		
		$search_query = "SELECT paper_id FROM paper 
			WHERE title LIKE '%$query_term%' OR abstract LIKE '%$query_term%'
			UNION
			SELECT paper_id FROM paperAuthorAssoc
			INNER JOIN author ON 
			(
				author.first_name LIKE '%$query_term%' OR
				author.middle_name LIKE '%$query_term%' OR
				author.last_name LIKE '%$query_term%'
			) AND
			paperAuthorAssoc.author_id=author.author_id";
	
		$paper_ids = $this->paper_db->get_col($search_query);
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
		
		$this->paper_db->delete( 'paperAuthorAssoc', array( 'paper_id' => $paper_id ));
		$this->paper_db->delete( 'paper', array( 'paper_id' => $paper_id ));
		
		//delete authors not tied to paper
		$this->paper_db->query("DELETE FROM author WHERE author_id NOT IN (SELECT author_id FROM paperAuthorAssoc)");
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
			$this->paper_db->delete( 'paperAuthorAssoc', array( 'paper_id' => $paper_id ));
			$this->paper_db->delete( 'paper', array( 'paper_id' => $paper_id ));
		}
		
		//delete authors not tied to paper
		$this->paper_db->query("DELETE FROM author WHERE author_id NOT IN (SELECT author_id FROM paperAuthorAssoc)");
	}
	
	public function get_all_papers() {
		$query = "SELECT * FROM paper";
		return $this->paper_db->get_results($query);
	}

	public function get_authors_by_initial($au){
		$query = "SELECT * FROM author where last_name like '$au%' order by last_name";
		return $this->paper_db->get_results($query,ARRAY_A);
	}
	public function get_author_paper_amount($au){
		$query = "select count(paper_id) from paperAuthorAssoc where author_id=".$au['author_id'];
		return $this->paper_db->get_var($query);
	}
	public function get_author_papers($au){
		//$query= "select * from paper inner join paperAuthorAssoc on paperAuthorAssoc.author_id=".$au;
		$query="select * from paper where paper_id in (select paper_id from paperAuthorAssoc where author_id=".$au.")";
		return $this->paper_db->get_results($query,ARRAY_A);
	}

	//song Teng 
	public function get_all_years() {
		$query = "SELECT DISTINCT publication_year FROM paper";
		return $this->paper_db->get_results($query);
	}
	// Song Teng 
	public function get_all_papers_by_year($year) {
		$query = "SELECT * FROM paper WHERE publication_year = $year ORDER by title";
		return $this->paper_db->get_results($query);
	}

	// Xiaoran
	public function get_all_papers_by_type($type) {
	
		$query = "SELECT * FROM paper WHERE paper.type = '" . $type . "' ORDER BY title Asc";
		return $this->paper_db->get_results($query);
	
	}

	//xiaoran
	public function get_paper_detail_url_by_paperID($id) {
		
		$query = "SELECT guid FROM wp_posts WHERE ID IN (SELECT post_id FROM wp_postmeta WHERE meta_key = 'paper_id' AND meta_value = '" . $id . "')";
		return $this->post_db->get_row($query);
		
	}
	public function add_new_paper($values) {
       
        $this->paper_db->insert( 
			'paper', 
			array( 
				'title' => trim($values['title']),
				'abstract' => trim($values['abstract']),
				'type' => trim($values['type']),
				'publication_year' => $values['year']
			), 
			array( 
				'%s',
				'%s',
				'%s',
				'%s',
				'%d'
			) 
		);
		$paper_id = $this->paper_db->insert_id;
		
		$author_ids = $values['existing_authors'];
		
		//Insert new authors
		foreach ($values['new_authors'] as $new_author) {
			$this->paper_db->insert(
				'author',
				array(
					'first_name' => trim($new_author['first_name']),
					'middle_name' => trim($new_author['middle_name']),
					'last_name' => trim($new_author['last_name']),
					'suffix' => trim($new_author['suffix'])
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s'
				)
			);
			$new_author_id = $this->paper_db->insert_id;
			array_push($author_ids, $new_author_id);
		}
		
		//Add association between papers and authors
		foreach ($author_ids as $author_id) {
			$this->paper_db->insert(
				'paperAuthorAssoc',
				array(
					'paper_id' => $paper_id,
					'author_id' => $author_id
				),
				array(
					'%d',
					'%d'
				)
			);
		}
		
		$user_id = get_current_user_id();
		$new_post = array(
			'post_title' => trim($values['title']),
			'post_content' => '',
			'post_status' => 'publish',
			'post_date' => date('Y-m-d H:i:s'),
			'post_author' => $user_id,
			'post_type' => 'post',
			'post_category' => array(0)
		);
		$post_id = wp_insert_post($new_post);
		add_post_meta($post_id, 'paper_id', $paper_id);
		
		$this->process_file_upload($paper_id, trim($values['title']), $values['file']);
		
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
        $title = trim($new_values['title']);
        $this->paper_db->update( 
			'paper', 
			array( 
				'title' => $title,
				'abstract' => trim($new_values['abstract']),
				'type' => trim($new_values['type']),
				'publication_year' => $new_values['year']
			), 
			array(
				'paper_id' => $paper_id
			),
			array( 
				'%s',
				'%s',
				'%s',
				'%d'
			),
			array(
				'%d'
			)
		);
		
		$author_ids = $new_values['existing_authors'];
		
		//Insert new authors
		foreach ($new_values['new_authors'] as $new_author) {
			$this->paper_db->insert(
				'author',
				array(
					'first_name' => trim($new_author['first_name']),
					'middle_name' => trim($new_author['middle_name']),
					'last_name' => trim($new_author['last_name']),
					'suffix' => trim($new_author['suffix'])
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s'
				)
			);
			$new_author_id = $this->paper_db->insert_id;
			array_push($author_ids, $new_author_id);
		}
		
		$already_added_authors = $this->paper_db->get_col("SELECT author_id FROM paperAuthorAssoc WHERE paper_id=$paper_id");
		
		//Add association between papers and authors
		foreach ($author_ids as $author_id) {
			
			$already_added = in_array($author_id, $already_added_authors);
		
			if ($already_added === false) {
				$this->paper_db->insert(
					'paperAuthorAssoc',
					array(
						'paper_id' => $paper_id,
						'author_id' => $author_id
					),
					array(
						'%d',
						'%d'
					)
				);
			}
		}
		
		foreach ($already_added_authors as $author_id) {
			$author_removed = (in_array($author_id, $author_ids) === false);
		
			if ($author_removed) {
				$this->paper_db->delete(
					'paperAuthorAssoc',
					array(
						'paper_id' => $paper_id,
						'author_id' => $author_id
					),
					array(
						'%d',
						'%d'
					)
				);
			}
		}
		
		//delete authors not tied to paper
		$this->paper_db->query("DELETE FROM author WHERE author_id NOT IN (SELECT author_id FROM paperAuthorAssoc)");

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
    
    public function get_all_authors() {
    	$query = "SELECT author_id, first_name, middle_name, last_name, suffix FROM author ORDER BY first_name ASC";
    	$results = $this->paper_db->get_results($query, ARRAY_A);
    	foreach ($results as $key => $author) {
    		$results[$key]['full_name'] = $this->get_author_fullname($author);
    	}	
    	return $results;
    }
}

register_activation_hook( __FILE__, array('TechReports','plugin_setup'));

add_action('admin_menu', array('TechReports','tech_reports_admin_actions'));

		//zongmin
		add_shortcode( 'List_Paper_By_Author_Name', array('TechReports', 'tech_reports_guest_view_paper_by_author_name') );

		add_shortcode( 'List_Paper_By_Year', array('TechReports', 'tech_reports_guest_view_paper_by_year') );

		// Xiaoran
		add_shortcode( 'List_Paper_By_Type', array('TechReports', 'tech_reports_guest_view_paper_by_type') );
?>
