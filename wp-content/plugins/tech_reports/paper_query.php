<?php

/**
 * This class provides a convenient wrapper around 
 * a query that reads papers from the DB. It also 
 * provides pagination options.
 */

class PaperQuery {
	
	private $query;
	private $page_args;
	private $total_results = 0;
	private $queried_papers;
	private $is_single = false;
	private $total_page_count = 0;
	
	function __construct($query=NULL, $page_args=NULL) {
		$this->query = $query;
		$this->page_args = $page_args;
		$this->paper_db = new wpdb(DB_USER, DB_PASSWORD, PAPER_DB_NAME, DB_HOST);
		
		if (is_null($this->query)) {
			$this->set_as_empty();
		} else if (is_null($this->page_args)) {
			$this->run_query();
		} else {
			$this->run_paged_query();
		}
	}
	
	private function set_as_empty() {
		$this->total_results = 0;
		$this->queried_papers = array();
		$this->is_single = false;
		$this->total_page_count = 0;
	}
	
	public function set_single($is_single) {
		$this->is_single = $is_single;
	}
	
	public function is_single() {
		return $this->is_single;
	}

	public function get_total_results() {
		return $this->total_results;
	}
	
	public function get_total_pages() {
		return $this->total_page_count;
	}
	
	private function run_query() {
		$this->queried_papers = $this->get_papers_from_query($this->query);
	}
	
	private function run_paged_query() {
    	$count_query = "SELECT DISTINCT paper.paper_id FROM (" . $this->query . ") as paper";
		$paper_ids = $this->paper_db->get_col($count_query);
		
		$this->total_results = count($paper_ids);
		
		if ($this->total_results === 0) {
			$this->queried_papers = array();
			return;
		}
		
		$paged_ids = array_slice($paper_ids, ($this->page_args['current_page'] - 1)*$this->page_args['per_page'], $this->page_args['per_page']); 
		
		$query = "SELECT * FROM (" . $this->query . ") as paper WHERE paper.paper_id IN (" . implode(', ', $paged_ids) . ")";
		
		$this->total_page_count = ceil($this->total_results / $this->page_args['per_page']);
		$this->queried_papers = $this->get_papers_from_query($query);
		$this->is_single = false;
	}
	
	public function get_papers() {
		return $this->queried_papers;
	}
	
	public function get_next_paper() {
		return array_shift($this->queried_papers);
	}
	
	public function has_results() {
		return ($this->queried_papers !== NULL && count($this->queried_papers) > 0);
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
			 	  	'download_count' => $row['download_count'],
			 	  	'authors' => array()
		 	  	);
			}
			
			$author = array(
				'author_id' => $row['author_id'], 
				'first_name' => $row['first_name'], 
				'middle_name' => $row['middle_name'],
				'last_name' => $row['last_name'], 
				'suffix' => $row['suffix'],
				'author_index' => $row['author_index']
			);
			$author['full_name'] = self::get_author_fullname($author);
			
			$results_array[$row['paper_id']]['authors'][] = $author;
		}
		
		foreach ($results_array as $index => $paper) {
			$results_array[$index]['citation'] = $this->generate_citation($paper);
			$results_array[$index]['identifier'] = $this->get_paper_identifier($paper['paper_id'], $paper['publication_year']);
			$results_array[$index]['url'] = $this->get_paper_url($paper['paper_id'], $paper['publication_year']);
		}
		
		return array_values($results_array);
    }
    
    private function generate_citation($paper) {
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
       $citation .= ". \"<i>" . $paper['title'] . "</i>\". ";
       if ($paper['published_at'] !== NULL && strlen($paper['published_at']) > 0) {
       		$citation .= $paper['published_at'] . ", ";
       }
       
       $citation .=  $paper['publication_year'] . ".";
       return $citation;
    }
    
    public static function get_author_fullname($author) {
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
	
	private function get_paper_identifier($paper_id, $publication_year=NULL) {
    	
    	if (is_null($publication_year)) {
			$publication_year = $this->paper_db->get_var("SELECT publication_year FROM paper WHERE paper_id=$paper_id");
		}
    	
    	$filename = "USC-CSSE-";
    	$filename .= strval($publication_year);
    	$filename .= "-" . strval($paper_id);
    	
    	return $filename;
    }
    
    private function get_paper_url($paper_id, $publication_year=NULL) {
	 	$paper_identifier = $this->get_paper_identifier($paper_id, $publication_year);
		$base_url = plugin_dir_url(__FILE__);
		return $base_url . "uploads/$paper_identifier.pdf";
	}
}

?>
