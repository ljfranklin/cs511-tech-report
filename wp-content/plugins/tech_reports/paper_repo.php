<?php

require_once('paper_query.php');
require_once('author_query.php');

/**
 * This class is the primary wrapper for the research paper database.
 * Most of the time, it is preferable to create a PaperQuery or AuthorQuery
 * to read from the DB, although sometimes querying directly is easier.
 */

class PaperRepo {

	private $paper_db;

	function __construct() {
		$this->paper_db = new wpdb(DB_USER, DB_PASSWORD, PAPER_DB_NAME, DB_HOST);
	}
	
	public function create_plugin_table() {
		if($this->paper_db->get_var("SHOW TABLES LIKE 'paper'") !== 'paper') {
			$sql = "CREATE TABLE if not exists paper (
				paper_id INT NOT NULL AUTO_INCREMENT,
				year_id INT NOT NULL,
				title TEXT NOT NULL,
				abstract TEXT NOT NULL,
				publication_year YEAR NOT NULL,
				published_at TEXT NULL,
				keywords TEXT NULL,
				type VARCHAR(40) NOT NULL,
				download_count INT NOT NULL DEFAULT 0,
				PRIMARY KEY (paper_id),
				UNIQUE KEY (year_id, publication_year)
				);";
			$this->paper_db->query($sql);
		}
		if($this->paper_db->get_var("SHOW TABLES LIKE 'author'") !== 'author') {
			$sql = "CREATE TABLE author (
				author_id INT NOT NULL AUTO_INCREMENT,
				first_name TEXT NOT NULL, 
				middle_name TEXT NULL, 
				last_name TEXT NOT NULL,
				suffix VARCHAR(10) NULL,
				PRIMARY KEY (author_id)
				);";
			$this->paper_db->query($sql);
		}
		if($this->paper_db->get_var("SHOW TABLES LIKE 'paperAuthorAssoc'") !== 'paperAuthorAssoc') {
			$sql = "CREATE TABLE if not exists paperAuthorAssoc (
				author_id INT NOT NULL,
				paper_id INT NOT NULL,
				author_index INT NOT NULL,
				PRIMARY KEY (author_id, paper_id),
				FOREIGN KEY (author_id) REFERENCES author(author_id),
				FOREIGN KEY (paper_id) REFERENCES paper(paper_id)
				);";
			$this->paper_db->query($sql);
		}
	}
    
    public function delete_paper($paper_id) {
		
		unlink($this->get_paper_filename_by_id($paper_id));
		
		$this->paper_db->delete( 'paperAuthorAssoc', array( 'paper_id' => $paper_id ));
		$this->paper_db->delete( 'paper', array( 'paper_id' => $paper_id ));
		
		$this->delete_authors_without_papers();
	}

	public function delete_multiple_papers($paper_ids) {
		
		foreach ($paper_ids as $paper_id){
			unlink($this->get_paper_filename_by_id($paper_id));
			$this->paper_db->delete( 'paperAuthorAssoc', array( 'paper_id' => $paper_id ));
			$this->paper_db->delete( 'paper', array( 'paper_id' => $paper_id ));
		}
		
		$this->delete_authors_without_papers();
	}
	
	private function get_paper_filename_by_id($paper_id) {
	
    	$plugin_dir = plugin_dir_path( __FILE__ );
    
    	$query = new PaperQuery();
    	$identifier = $query->get_paper_identifier_by_id($paper_id);
    	
    	return sprintf('%suploads/%s.pdf',
    		$plugin_dir,
    	    $identifier
    	);
    }
    
    private function get_paper_filename($year_id, $publication_year) {
	
    	$plugin_dir = plugin_dir_path( __FILE__ );
    
    	$query = new PaperQuery();
    	$identifier = $query->get_paper_identifier($year_id, $publication_year);
    	
    	return sprintf('%suploads/%s.pdf',
    		$plugin_dir,
    	    $identifier
    	);
    }
	
	private function delete_authors_without_papers() {
		$this->paper_db->query("DELETE FROM author WHERE author_id NOT IN (SELECT author_id FROM paperAuthorAssoc)");
	}
    
    public function add_new_paper($values) {
       	
       	$year_id = empty($values['year_id']) ? $this->get_next_id($values['publication_year']) : $values['year_id'];   
       	
        $success = $this->paper_db->insert( 
			'paper', 
			array( 
				'year_id' => $year_id,
				'title' => trim($values['title']),
				'abstract' => trim($values['abstract']),
				'type' => trim($values['type']),
				'publication_year' => $values['publication_year'],
				'published_at' => trim($values['published_at']),
				'keywords' => trim($values['keywords'])
			), 
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s'
			) 
		);
		$paper_id = $this->paper_db->insert_id;
		
		if ($success === false) {
			return NULL;
		}
		
		$this->add_authors($paper_id, $values['new_authors'], $values['existing_authors']);
		
		$this->process_file_upload($year_id, trim($values['publication_year']), $values['file']);
		
		return $paper_id;
    }
    
    private function get_next_id($publication_year) {
    	$highest_id = $this->paper_db->get_var(
    		"SELECT IFNULL(MAX(year_id), 499) FROM paper WHERE publication_year=$publication_year"
    	);
    	return ($highest_id + 1);
    }
    
    private function add_authors($paper_id, $new_authors, $existing_authors) {
    	$authors = $existing_authors;
		
		//Insert new authors
		foreach ($new_authors as $new_author) {
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
			$new_author['author_id'] = $new_author_id;
			array_push($authors, $new_author);
		}
		
		//Add association between papers and authors
		foreach ($authors as $author) {
			$this->paper_db->insert(
				'paperAuthorAssoc',
				array(
					'paper_id' => $paper_id,
					'author_id' => $author['author_id'],
					'author_index' => $author['author_index']
				),
				array(
					'%d',
					'%d',
					'%d'
				)
			);
		}
    }
    
    private function process_file_upload($year_id, $year, $file) {
    
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
        	$this->get_paper_filename($year_id, $year)
    	)) {
    	    throw new RuntimeException('Failed to move uploaded file.');
    	}
    }
    
    private function delete_old_file($year_id, $old_year) {
    	unlink($this->get_paper_filename($year_id, $old_year));
    }
    
    private function rename_old_file($old_year_id, $old_year, $year_id, $new_year) {
    	rename($this->get_paper_filename($old_year_id, $old_year), $this->get_paper_filename($year_id, $new_year));
    }
    
    public function update_paper($new_values) {
        
        $paper_id = $new_values['paper_id'];
        $year_id = $new_values['year_id'];
        $title = trim($new_values['title']);
        
        $old_year = $new_values['previous_year'];
        $old_year_id = $new_values['previous_year_id'];
        $year = trim($new_values['publication_year']);
        $this->paper_db->update( 
			'paper', 
			array( 
				'title' => $title,
				'year_id' => $year_id,
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
				'%d',
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
		
		//remove existing associations
		$this->paper_db->delete(
			'paperAuthorAssoc',
			array(
				'paper_id' => $paper_id
			),
			array(
				'%d'
			)
		);
		
		$this->add_authors($paper_id, $new_values['new_authors'], $new_values['existing_authors']);
		
		//delete authors not tied to paper
		$this->paper_db->query("DELETE FROM author WHERE author_id NOT IN (SELECT author_id FROM paperAuthorAssoc)");
		
		if (empty($new_values['file']['tmp_name']) === false) {
			$this->process_file_upload($year_id, $year, $new_values['file']);
			if ($old_year !== $year || $old_year_id !== $year_id) {
				$this->delete_old_file($old_year_id, $old_year);
			}
		}
		
		if (empty($new_values['file']['tmp_name']) && ($old_year !== $year || $old_year_id !== $year_id)) {
    		$this->rename_old_file($old_year_id, $old_year, $year_id, $year);
    	}
		
		return $paper_id;
    }
    
    public function increment_download_counter($paper_id) {
    	$this->paper_db->query("UPDATE paper SET download_count=download_count+1 WHERE paper_id=$paper_id");
    }
    
    public function get_author_initials() {
    
    	$last_names = $this->paper_db->get_col(
    		"SELECT DISTINCT last_name FROM author ORDER BY last_name ASC"
    	);
    
    	$initials = array();
    	foreach ($last_names as $name) {
    		$first_initial = substr($name, 0, 1);
    		if (in_array($first_initial, $initials) === false) {
    			$initials[] = $first_initial;
    		}
    	}
    	return $initials;
    }
    
    public function get_all_authors() {
    	$query = "SELECT author_id, first_name, middle_name, last_name, suffix FROM author ORDER BY first_name ASC";
    	$results = $this->paper_db->get_results($query, ARRAY_A);
    	foreach ($results as $key => $author) {
    		$results[$key]['full_name'] = PaperQuery::get_author_fullname($author);
    	}	
    	return $results;
    }
    
    public function query_papers_by_author($page_args) {
    	$query = "SELECT paper.*, author.*, paperAuthorAssoc.author_index FROM paper 
    		INNER JOIN paperAuthorAssoc ON paper.paper_id=paperAuthorAssoc.paper_id
    		INNER JOIN author ON author.author_id=paperAuthorAssoc.author_id
    		ORDER BY paperAuthorAssoc.author_index ASC, paper.publication_year DESC, paper.year_id DESC";
    	
    	return new AuthorQuery($query, $page_args);
    }
    
    public function query_recent_papers($page_args) {
    	$query = "SELECT paper.*, author.*, paperAuthorAssoc.author_index FROM paper 
    		INNER JOIN paperAuthorAssoc ON paper.paper_id=paperAuthorAssoc.paper_id 
    		INNER JOIN author ON author.author_id=paperAuthorAssoc.author_id
    		ORDER BY paper.paper_id DESC, paperAuthorAssoc.author_index ASC";
	
		return new PaperQuery($query, $page_args);
    }
    
    public function query_single_paper($paper_id) {
    
    	$query = $this->get_single_paper_query($paper_id);
    		
		$paper_query = new PaperQuery($query);
		$paper_query->set_single(true);
    	
    	return $paper_query;
    }
    
    public function query_papers_by_year($year = NULL) {
    	if ($year === NULL) {
    		$year = $this->get_most_recent_year();
    	}
    	if ($year === NULL) {
    		return new PaperQuery();
    	}
    	
    	$query = $this->get_by_year_query($year);
    	
    	return new PaperQuery($query);
    }
    
    public function query_papers_by_search($query_term, $page_args) {
    
    	$query_term = strtolower($query_term);
    
    	$id_prefix = 'usc-csse-';
    	$contains_id = strpos($query_term, $id_prefix);
    	if ($contains_id !== false) {
    		$ids = explode('-', substr($query_term, strlen($id_prefix)));
    		
    		if (count($ids) === 2 && is_numeric($ids[0]) && is_numeric($ids[1])) {
    			$publication_year = $ids[0];
    			$year_id = $ids[1];
    		} else {
    			$publication_year = 1900;
    			$year_id = 0;
    		}
    			
    		$query = "SELECT paper.*, author.*, paperAuthorAssoc.author_index FROM paper 
				INNER JOIN paperAuthorAssoc ON paper.paper_id=paperAuthorAssoc.paper_id 
				INNER JOIN author ON author.author_id=paperAuthorAssoc.author_id
				WHERE (publication_year=$publication_year AND year_id=$year_id)
				ORDER BY paper.paper_id DESC, paperAuthorAssoc.author_index ASC";
    	} else {
    		$query = "SELECT paper.*, author.*, paperAuthorAssoc.author_index FROM paper 
				INNER JOIN paperAuthorAssoc ON paper.paper_id=paperAuthorAssoc.paper_id 
				INNER JOIN author ON author.author_id=paperAuthorAssoc.author_id
				WHERE (title LIKE '%$query_term%' OR abstract LIKE '%$query_term%' OR published_at LIKE '%$query_term%' OR keywords LIKE '%$query_term%' OR
				first_name LIKE '%$query_term%' OR
				middle_name LIKE '%$query_term%' OR
				last_name LIKE '%$query_term%')
				ORDER BY paper.publication_year DESC, paper.year_id DESC";
    	}
	
		return new PaperQuery($query, $page_args);
    }
    
    public function get_all_paper_years() {
    	return $this->paper_db->get_col(
    		"SELECT DISTINCT publication_year FROM paper ORDER BY publication_year DESC"
    	);
    }
    
    private function get_all_papers_query() {
    	return "SELECT paper.*, author.*, paperAuthorAssoc.author_index FROM paper 
    		INNER JOIN paperAuthorAssoc ON paper.paper_id=paperAuthorAssoc.paper_id 
    		INNER JOIN author ON author.author_id=paperAuthorAssoc.author_id";
    }
    
    private function get_single_paper_query($paper_id) {
    	return "SELECT paper.*, author.*, paperAuthorAssoc.author_index FROM paper
    		INNER JOIN paperAuthorAssoc ON paper.paper_id=paperAuthorAssoc.paper_id 
    		INNER JOIN author ON author.author_id=paperAuthorAssoc.author_id
    		WHERE paper.paper_id=$paper_id
    		ORDER BY paperAuthorAssoc.author_index ASC";
    }
    
    public function get_most_recent_year() {
    	return $this->paper_db->get_var(
    		"SELECT publication_year FROM paper ORDER BY publication_year DESC LIMIT 1"
    	);
    }
    
    private function get_by_year_query($year) {
    	return "SELECT paper.*, author.*, paperAuthorAssoc.author_index FROM paper
    		INNER JOIN paperAuthorAssoc ON paper.paper_id=paperAuthorAssoc.paper_id 
    		INNER JOIN author ON author.author_id=paperAuthorAssoc.author_id
    		WHERE paper.publication_year=$year
    		ORDER BY paper.publication_year DESC, paper.year_id DESC";
    }
}

?>
