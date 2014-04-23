<?php

require_once('paper_query.php');

/**
 * This class provides a convenient wrapper around 
 * a query that reads authors from the DB. It also 
 * provides pagination options.
 */

class AuthorQuery {
	private $query;
	private $page_args;
	private $queried_authors;
	private $is_single = false;
	
	function __construct($query=NULL, $page_args=NULL) {
		$this->query = $query;
		$this->page_args = $page_args;
		$this->paper_db = new wpdb(DB_USER, DB_PASSWORD, PAPER_DB_NAME, DB_HOST);
		
		if (is_null($this->page_args)) {
			$this->run_query();
		} else {
			$this->run_paged_query();
		}
	}
	
	public function get_authors() {
		return $this->queried_authors;
	}
	
	public function get_next_author() {
		return array_shift($this->queried_authors);
	}
	
	public function has_results() {
		return ($this->queried_authors !== NULL && count($this->queried_authors) > 0);
	}
	
	public function is_single() {
		return $this->is_single;
	}
	
	private function run_query() {
		$author_to_papers = array();
		
		$paper_query = new PaperQuery($this->query);
		$papers = $paper_query->get_papers();
		
    	foreach ($papers as $paper) {
    		foreach ($paper['authors'] as $author) {
				$author_id = $author['author_id'];
				if(!array_key_exists($author_id, $author_to_papers)) {
					$author_to_papers[$author_id] = $author;
				}
				
				$author_to_papers[$author_id]['papers'][] = $paper;
    		}
    	}
    	
    	$this->sort_authors($author_to_papers);
    	
    	$this->queried_authors = array_values($author_to_papers);
	}
	
	private function run_paged_query() {
		$author_to_papers = array();
		
		$first_letter = $this->page_args['page_letter'];
		
		$query = $this->query;
		$query = "SELECT * FROM (" . $query . ") WHERE paper.paper_id IN (
					SELECT DISTINCT paperAuthorAssoc.paper_id FROM author
					INNER JOIN paperAuthorAssoc ON author.author_id=paperAuthorAssoc.author_id
				 	WHERE author.last_name LIKE '$first_letter%'
				)";
		
		$paper_query = new PaperQuery($this->query);
		$papers = $paper_query->get_papers();
		
    	foreach ($papers as $paper) {
    		foreach ($paper['authors'] as $author) {
				if (substr($author['last_name'], 0, 1) === $first_letter) {
					$author_id = $author['author_id'];
					if(!array_key_exists($author_id, $author_to_papers)) {
						$author_to_papers[$author_id] = $author;
					}
					
					$author_to_papers[$author_id]['papers'][] = $paper;
				}
    		}
    	}
    	
    	$this->sort_authors($author_to_papers);
    	
    	$this->queried_authors = array_values($author_to_papers);
	}
	
	private function sort_authors($author_to_papers) {
		$name_cmp = function($a, $b) {
    		return strcmp($a['last_name'], $b['last_name']);
    	};
    	usort($author_to_papers, $name_cmp);
	}
}

?>
