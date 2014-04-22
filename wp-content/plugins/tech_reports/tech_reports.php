<?php
/**
 * Plugin Name: Technical Reports Plugin
 * Description: A plugin to enable saving and displaying of research papers 
 */
 
class TechReports {

	private $paper_db;
	private $post_db;
	private $paper_values = NULL;
	private $queried_papers = NULL;
	private $queried_authors = NULL;
	private $current_paper = NULL;
	private $current_author = NULL;
	private $is_single = false;
	private $total_page_count = 0;

	function __construct($paper_id=NULL) {
		$this->paper_db = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
		$this->post_db = new wpdb("wordpress", "wp1234", "wordpress", "localhost");
	}
	
	public static function plugin_setup() {
		self::create_plugin_table();
		self::create_upload_directory();
		self::activate_theme();
		self::add_by_authors_page();
		self::add_by_year_page();
		self::update_blog_description();
		
		add_action('update_option_active_plugins', array('TechReports','activate_extra_plugins'));
	}

	private static function create_plugin_table() {
		$paper_db = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
		if($paper_db->get_var("SHOW TABLES LIKE 'paper'") !== 'paper') {
			$sql = "CREATE TABLE if not exists paper (
				paper_id INT NOT NULL AUTO_INCREMENT, 
				title TEXT NOT NULL,
				abstract TEXT NOT NULL,
				publication_year YEAR NOT NULL,
				published_at TEXT NULL,
				keywords TEXT NULL,
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
	
	private static function update_blog_description() {
		update_option('blogdescription', 'A system for the storage of research papers from CSSE');
	}
	
	private static function add_by_authors_page() {
		add_shortcode( 'List_Paper_By_Author_Name', array('TechReports', 'tech_reports_guest_view_paper_by_author_name') );

		if (get_page_by_title('Papers By Author') == NULL) {
			$page['post_type']    = 'page';
			$page['post_content'] = '\[List_Paper_By_Author_Name\]';
			$page['post_parent']  = 0;
			$page['post_status']  = 'publish';
			$page['post_title']   = 'Papers By Author';
			wp_insert_post ($page);
		}
	}
	
	private static function add_by_year_page() {
		add_shortcode( 'List_Paper_By_Year', array('TechReports', 'tech_reports_guest_view_paper_by_year') );

		if (get_page_by_title('Papers By Year') == NULL) {
			$page1['post_type']    = 'page';
			$page1['post_content'] = '\[List_Paper_By_Year\]';
			$page1['post_parent']  = 0;
			$page1['post_status']  = 'publish';
			$page1['post_title']   = 'Papers By Year';
			wp_insert_post ($page1);
		}
	}
	
	public static function tech_reports_guest_view_paper_by_author_name () {
		include('tech_reports_guest_view_paper_by_author_name.php');	
	}

	public static function tech_reports_guest_view_paper_by_year() {
		include('tech_reports_guest_view_paper_by_year.php');	
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
	
	public static function activate_extra_plugins() {
	
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		
		self::activate_disable_comments();
		self::activate_dashboard_plugin();
	}

	private static function activate_disable_comments(){
		$plugin_full_path = ABSPATH . 'wp-content/plugins/disable-comments/disable-comments.php';
		if(is_plugin_inactive($plugin_full_path)) {
			activate_plugin($plugin_full_path);
		}
	}
	
	private static function activate_dashboard_plugin() {
		$plugin_full_path = ABSPATH . 'wp-content/plugins/Delete/delete.php';
		if(is_plugin_inactive($plugin_full_path)) {
			activate_plugin($plugin_full_path);
		}
	}

	public static function tech_reports_admin_edit() {
    	include('tech_reports_admin_edit.php');
	}
	
	public static function tech_reports_admin_list() {
		include('tech_reports_admin_list.php');	
	}

	public static function tech_reports_admin_actions() {
 		add_menu_page("Research Papers", "Research Papers", "edit_posts", "list-papers", array("TechReports", "tech_reports_admin_list"), '', 0);
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
		
		$paper['identifier'] = $this->get_paper_identifier($paper['paper_id'], $paper['publication_year']);
		
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
    
    private function get_paper_filename($paper_id, $publication_year=NULL) {
	
    	$plugin_dir = plugin_dir_path( __FILE__ );
    
    	$filename = $this->get_paper_identifier($paper_id, $publication_year);
    	
    	return sprintf('%suploads/%s.pdf',
    		$plugin_dir,
    	    $filename
    	);
    }
    
    private function get_paper_identifier($paper_id, $publication_year=NULL) {
    	
    	if (is_null($publication_year)) {
			$publication_year = $this->paper_db->get_var("SELECT publication_year FROM paper WHERE paper_id=$paper_id");
		}
    	
    	$filename = "USC-CSSE-";
    	$filename .= strval($publication_year);
    	$filename .= "-" . strval($paper_id);
    	
    	return $filename;
    }
    
    private function get_paper_url($paper) {
	 	$pdf_path = $this->get_paper_filename($paper['paper_id'], $paper['publication_year']);
		$base_path = ABSPATH;
		return "../" . substr($pdf_path, strlen($base_path));
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
		$query="select * from paper where paper_id in (select paper_id from paperAuthorAssoc where author_id=".$au.")";
		return $this->paper_db->get_results($query,ARRAY_A);
	}

	public function get_all_years() {
		$query = "SELECT DISTINCT publication_year FROM paper";
		return $this->paper_db->get_results($query, ARRAY_A);
	}
	
	public function get_all_papers_by_year($year) {
		$query = "SELECT * FROM paper WHERE publication_year = $year ORDER by title";
		return $this->paper_db->get_results($query, ARRAY_A);
	}

	public function get_all_papers_by_type($type) {
	
		$query = "SELECT * FROM paper WHERE paper.type = '" . $type . "' ORDER BY title Asc";
		return $this->paper_db->get_results($query);
	}

	public function generate_citation($paper) {
       $citation = "";
       $authors = $paper['authors'];
       $num_authors = count($authors);

       $citation .= $authors[0]['first_name'] . " " . $authors[0]['last_name'];
       
       if ($num_authors > 1) {
           for ($i=1;$i<$num_authors-1;$i++) {
               $author = $authors[$i];
               $citation .= ", " . $author['first_name'] . " " . $author['last_name'];
           }
           $citation .= " and " . $authors[$num_authors-1]['first_name'] . " " . $authors[$num_authors-1]['last_name'];                       
       }
       $citation .= ", \" " . $paper['title'] . "\", ";
       $citation .=  $paper['publication_year'] . ".";
       return $citation;
    }

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
				'publication_year' => $values['publication_year'],
				'published_at' => trim($values['published_at']),
				'keywords' => trim($values['keywords'])
			), 
			array( 
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s'
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
		
		$this->process_file_upload($paper_id, trim($values['publication_year']), $values['file']);
		
		return $paper_id;
    }
    
    private function process_file_upload($paper_id, $year, $file) {
    
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
        	$this->get_paper_filename($paper_id, $year)
    	)) {
    	    throw new RuntimeException('Failed to move uploaded file.');
    	}
    }
    
    private function delete_old_file($paper_id, $old_year) {
    	unlink($this->get_paper_filename($paper_id, $old_year));
    }
    
    private function rename_old_file($paper_id, $old_year, $new_year) {
    	rename($this->get_paper_filename($paper_id, $old_year), $this->get_paper_filename($paper_id, $new_year));
    }
    
    public function update_paper($new_values, $old_year) {
	    global $wpdb;
        
        $paper_id = $new_values['paper_id'];
        $title = trim($new_values['title']);
        $year = trim($new_values['publication_year']);
        $this->paper_db->update( 
			'paper', 
			array( 
				'title' => $title,
				'abstract' => trim($new_values['abstract']),
				'type' => trim($new_values['type']),
				'publication_year' => $year,
				'published_at' => trim($new_values['published_at']),
				'keywords' => trim($new_values['keywords'])
			), 
			array(
				'paper_id' => $paper_id
			),
			array( 
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s'
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
			$this->process_file_upload($paper_id, $year, $new_values['file']);
		}
		
		if (empty($new_values['file']['tmp_name']) && $old_year !== $year) {
    		$this->rename_old_file($paper_id, $old_year, $year);
    	} else if ($old_year !== $year) {
			$this->delete_old_file($paper_id, $old_year);
		} 
		
		return $paper_id;
    }
    
    public function get_all_authors() {
    	$query = "SELECT author_id, first_name, middle_name, last_name, suffix FROM author ORDER BY first_name ASC";
    	$results = $this->paper_db->get_results($query, ARRAY_A);
    	foreach ($results as $key => $author) {
    		$results[$key]['full_name'] = $this->get_author_fullname($author);
    	}	
    	return $results;
    }
    
    public function query_papers_by_author() {
    	$this->is_single = false;
    	$query = $this->get_all_papers_query() . ' ORDER BY author.last_name ASC';
    	
    	$papers = $this->get_papers_from_query($query);
    	
    	$author_to_papers = array();
    	foreach ($papers as $paper) {
    		foreach ($paper['authors'] as $author) {
    			$author_id = $author['author_id'];
    			if(!array_key_exists($author_id, $author_to_papers)) {
    				$author_to_papers[$author_id] = $author;
    			}
    			$author_to_papers[$author_id]['papers'][] = $paper;
    		}
    	}
    	
    	$this->queried_authors = array_values($author_to_papers);
    }
    
    public function query_recent_papers($limit) {
    	$query = $this->get_all_papers_query() . " ORDER BY paper_id DESC LIMIT $limit";
		$this->is_single = false;
    
    	$this->queried_papers = $this->get_papers_from_query($query);
    }
    
    public function query_papers($paper_id = NULL) {
    
    	if ($paper_id === NULL) {
    		$query = $this->get_all_papers_query() . ' ORDER BY paper_id DESC';
    		$this->is_single = false;
    	} else {
    		$query = $this->get_single_paper_query($paper_id);
    		$this->is_single = true;
    	}
    
    	$this->queried_papers = $this->get_papers_from_query($query);
    }
    
    public function query_papers_by_year($year = NULL) {
    	if ($year === NULL) {
    		$year = $this->get_most_recent_year();
    	}
    	
    	$query = $this->get_by_year_query($year);
    	
    	$this->queried_papers = $this->get_papers_from_query($query);
    }
    
    public function query_papers_by_search($query_term, $page_args) {
    
    	$query = "SELECT paper.*, author.* FROM paper 
    		INNER JOIN paperAuthorAssoc ON paper.paper_id=paperAuthorAssoc.paper_id 
    		INNER JOIN author ON author.author_id=paperAuthorAssoc.author_id
    		WHERE (title LIKE '%$query_term%' OR abstract LIKE '%$query_term%' OR published_at LIKE '%$query_term%' OR keywords LIKE '%$query_term%' OR
    		first_name LIKE '%$query_term%' OR
			middle_name LIKE '%$query_term%' OR
			last_name LIKE '%$query_term%')";
	
		$count_query = "SELECT DISTINCT paper.paper_id FROM ($query) as paper";
		$paper_ids = $this->paper_db->get_col($count_query);
		
		if (count($paper_ids) === 0) {
			$this->queried_papers = array();
			return;
		}
		
		$this->total_page_count = ceil(count($paper_ids) / $page_args['per_page']);
		$paged_ids = array_slice($paper_ids, ($page_args['current_page'] - 1)*$page_args['per_page'], $page_args['per_page']); 
		
		$query .= " AND paper.paper_id IN (" . implode(', ', $paged_ids) . ")";
		
		$this->queried_papers = $this->get_papers_from_query($query);
		
		$this->is_single = false;
    }
    
    public function get_all_paper_years() {
    	return $this->paper_db->get_col(
    		"SELECT DISTINCT publication_year FROM paper ORDER BY publication_year DESC"
    	);
    }
    
    private function get_papers_from_query($query) {
    	$results = $this->paper_db->get_results($query, ARRAY_A);
    	
    	$results_array = array();
		foreach($results as $row) {
			if(!array_key_exists($row['paper_id'], $results_array)) {
				$results_array[$row['paper_id']] = array(
				   	'paper_id' => $row['paper_id'], 
			 	  	'title' => $row['title'],
			 	  	'abstract' => $row['abstract'], 
			 	  	'publication_year' => $row['publication_year'], 
			 	  	'published_at' => $row['published_at'], 
			 	  	'keywords' => $row['keywords'], 
			 	  	'type' => $row['type'],
			 	  	'authors' => array()
		 	  	);
			}
			
			$author = array(
				'author_id' => $row['author_id'], 
				'first_name' => $row['first_name'], 
				'middle_name' => $row['middle_name'],
				'last_name' => $row['last_name'], 
				'suffix' => $row['suffix']
			);
			$author['full_name'] = $this->get_author_fullname($author);
			
			$results_array[$row['paper_id']]['authors'][] = $author;
		}
		
		foreach ($results_array as $index => $paper) {
			$results_array[$index]['citation'] = $this->generate_citation($paper);
			$results_array[$index]['identifier'] = $this->get_paper_identifier($paper['paper_id'], $paper['publication_year']);
			$results_array[$index]['file'] = $this->get_paper_url($paper);
		}
		
		return array_values($results_array);
    }
    
    private function get_all_papers_query() {
    	return "SELECT paper.*, author.* FROM paper 
    		INNER JOIN paperAuthorAssoc ON paper.paper_id=paperAuthorAssoc.paper_id 
    		INNER JOIN author ON author.author_id=paperAuthorAssoc.author_id";
    }
    
    private function get_single_paper_query($paper_id) {
    	return "SELECT paper.*, author.* FROM paper
    		INNER JOIN paperAuthorAssoc ON paper.paper_id=paperAuthorAssoc.paper_id 
    		INNER JOIN author ON author.author_id=paperAuthorAssoc.author_id
    		WHERE paper.paper_id=$paper_id";
    }
    
    public function get_most_recent_year() {
    	return $this->paper_db->get_var(
    		"SELECT publication_year FROM paper ORDER BY publication_year DESC LIMIT 1"
    	);
    }
    
    private function get_by_year_query($year) {
    	return "SELECT paper.*, author.* FROM paper
    		INNER JOIN paperAuthorAssoc ON paper.paper_id=paperAuthorAssoc.paper_id 
    		INNER JOIN author ON author.author_id=paperAuthorAssoc.author_id
    		WHERE paper.publication_year=$year
    		ORDER BY paper.paper_id DESC";
    }
    
    public function have_papers() {
    	return ($this->queried_papers !== NULL && count($this->queried_papers) > 0);
    }
    
    public function the_paper() {
    	$this->current_paper = array_shift($this->queried_papers);
    }
    
    public function get_paper_field($field_name) {
    	return $this->current_paper[$field_name];
    }
    
    public function is_single() {
    	return $this->is_single;
    }
    
    public function the_ID() {
    	return $this->current_paper['paper_id'];
    }
    
    public function get_permalink() {
    	return esc_url(get_site_url() . '/?paper=' . $this->current_paper['paper_id']);
    }
    
    public function have_authors() {
    	return ($this->queried_authors !== NULL && count($this->queried_authors) > 0);
    }
    
    public function the_author() {
    	$this->current_author = array_shift($this->queried_authors);
    }
    
    public function get_author_field($field_name) {
    	return $this->current_author[$field_name];
    }
    
    public function have_author_papers() {
    	return ($this->current_author['papers'] !== NULL && count($this->current_author['papers']) > 0);
    }
    
    public function the_author_paper() {
    	$this->current_paper = array_shift($this->current_author['papers']);
    }
    
    public function get_author_initials() {
    
    	$initials = array();
    	foreach ($this->queried_authors as $author) {
    		$first_initial = substr($author['last_name'], 0, 1);
    		if (in_array($first_initial, $initials) === false) {
    			$initials[] = $first_initial;
    		}
    	}
    	return $initials;
    }
    
    public function get_total_pages() {
    	return $this->total_page_count;
    }
}

register_activation_hook( __FILE__, array('TechReports','plugin_setup'));

add_action('admin_menu', array('TechReports','tech_reports_admin_actions'));

add_shortcode( 'List_Paper_By_Author_Name', array('TechReports', 'tech_reports_guest_view_paper_by_author_name') );

add_shortcode( 'List_Paper_By_Year', array('TechReports', 'tech_reports_guest_view_paper_by_year') );

?>
