<?php
/**
 * Plugin Name: Technical Reports Plugin
 * Description: A plugin to enable saving and displaying of research papers 
 */
 
 require_once("paper_repo.php");
 
class TechReports {

	private $post_db;
	private $paper_repo;
	private $paper_query = NULL;
	private $author_query = NULL;
	private $current_paper = NULL;
	private $current_author = NULL;

	function __construct() {
		global $wpdb;
	
		$this->paper_repo = new PaperRepo();
		$this->post_db = $wpdb;
		
		add_action('init', array($this,'process_download_link'));
	}
	
	public function get_paper_repo() {
		return $this->paper_repo;
	}
	
	public function process_download_link() {
		if(isset($_GET['download_paper'])) {
			$paper_id = $_GET['download_paper'];
			
			$this->paper_repo->increment_download_counter($paper_id);

			$paper_query = $this->paper_repo->query_single_paper($paper_id);
			$paper = $paper_query->get_papers()[0];
			$url = $paper['url'];
	
			wp_redirect($url);	
			exit;
		}
	}

	public function plugin_setup() {
		$this->create_plugin_table();
		$this->create_upload_directory();
		$this->activate_theme();
		$this->add_by_authors_page();
		$this->add_by_year_page();
		$this->update_blog_description();
		
		add_action('update_option_active_plugins', array($this,'activate_extra_plugins'));
	}
	
	private function create_plugin_table() {
		$this->paper_repo->create_plugin_table();
	}
	
	private function update_blog_description() {
		update_option('blogdescription', 'A system for the storage of research papers from CSSE');
	}
	
	private function add_by_authors_page() {
		add_shortcode( 'List_Paper_By_Author_Name', array($this, 'tech_reports_guest_view_paper_by_author_name') );

		if (get_page_by_title('Papers By Author') == NULL) {
			$page['post_type']    = 'page';
			$page['post_content'] = '\[List_Paper_By_Author_Name\]';
			$page['post_parent']  = 0;
			$page['post_status']  = 'publish';
			$page['post_title']   = 'Papers By Author';
			wp_insert_post ($page);
		}
	}
	
	private function add_by_year_page() {
		add_shortcode( 'List_Paper_By_Year', array($this, 'tech_reports_guest_view_paper_by_year') );

		if (get_page_by_title('Papers By Year') == NULL) {
			$page1['post_type']    = 'page';
			$page1['post_content'] = '\[List_Paper_By_Year\]';
			$page1['post_parent']  = 0;
			$page1['post_status']  = 'publish';
			$page1['post_title']   = 'Papers By Year';
			wp_insert_post ($page1);
		}
	}
	
	public function tech_reports_guest_view_paper_by_author_name () {
		include('tech_reports_guest_view_paper_by_author_name.php');	
	}

	public function tech_reports_guest_view_paper_by_year() {
		include('tech_reports_guest_view_paper_by_year.php');	
	}
	
	private function create_upload_directory() {
		$plugin_dir = plugin_dir_path( __FILE__ );
		$upload_path = $plugin_dir . "uploads";
		if (!file_exists($upload_path)) {
    		mkdir($upload_path, 0775);
		}
	}
	
	private function activate_theme() {
		switch_theme('ridizain-tech-report');
	}
	
	public function activate_extra_plugins() {
	
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		
		$this->activate_disable_comments();
		$this->activate_dashboard_plugin();
	}

	private function activate_disable_comments(){
		$plugin_full_path = ABSPATH . 'wp-content/plugins/disable-comments/disable-comments.php';
		if(is_plugin_inactive($plugin_full_path)) {
			activate_plugin($plugin_full_path);
		}
	}
	
	private function activate_dashboard_plugin() {
		$plugin_full_path = ABSPATH . 'wp-content/plugins/Delete/delete.php';
		if(is_plugin_inactive($plugin_full_path)) {
			activate_plugin($plugin_full_path);
		}
	}

	public function tech_reports_admin_edit() {
    	include('tech_reports_admin_edit.php');
	}
	
	public function tech_reports_admin_list() {
		include('tech_reports_admin_list.php');	
	}

	public function tech_reports_admin_actions() {
 		add_menu_page("Research Papers", "Research Papers", "edit_posts", "list-papers", array($this, "tech_reports_admin_list"), '', 0);
 		add_submenu_page("list-papers", "All Papers", "All Papers", "edit_posts", "list-papers", array($this, "tech_reports_admin_list"));
 		add_submenu_page("list-papers", "New Paper", "Add Paper", "edit_posts", "upload-paper", array($this, "tech_reports_admin_edit"));
	}
	
	public function query_single_paper($paper_id) {
		$this->paper_query = $this->paper_repo->query_single_paper($paper_id);
	}
	
	public function query_recent_papers($page_args) {
		$this->paper_query = $this->paper_repo->query_recent_papers($page_args);
	}
	
	public function query_papers_by_year($year=NULL) {
		$this->paper_query = $this->paper_repo->query_papers_by_year($year);
	}
	
	public function query_papers_by_search($query_term, $page_args) {
		$this->paper_query = $this->paper_repo->query_papers_by_search($query_term, $page_args);
	}
	
	public function query_papers_by_author($first_letter) {
		$page_args = array(
    		"page_letter" => $first_letter
    	);
		$this->author_query = $this->paper_repo->query_papers_by_author($page_args);
	}
    
    public function get_all_queried_papers() {
    	return $this->paper_query->get_papers();
    }
    
    public function have_papers() {
    	return $this->paper_query->has_results();
    }
    
    public function the_paper() {
    	$this->current_paper = $this->paper_query->get_next_paper();
    }
    
    public function get_paper_field($field_name) {
    	return $this->current_paper[$field_name];
    }
    
    public function is_single() {
    	return is_null($this->paper_query) ? false : $this->paper_query->is_single();
    }
    
    public function the_ID() {
    	return $this->current_paper['paper_id'];
    }
    
    public function get_permalink() {
    	return esc_url(get_site_url() . '/?paper=' . $this->current_paper['paper_id']);
    }
    
    public function have_authors() {
    	return $this->author_query->has_results();
    }
    
    public function the_author() {
    	$this->current_author = $this->author_query->get_next_author();
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
    
    public function get_total_pages() {
    	return $this->paper_query->get_total_pages();
    }
    
    public function get_total_results() {
    	return $this->paper_query->get_total_results();
    }
    
    public function get_download_link() {
    	return site_url() . "/?download_paper=" .$this->the_ID();
    }
}

$tech_report = new TechReports();
register_activation_hook( __FILE__, array($tech_report,'plugin_setup'));
add_action('admin_menu', array($tech_report,'tech_reports_admin_actions'));
add_shortcode( 'List_Paper_By_Author_Name', array($tech_report, 'tech_reports_guest_view_paper_by_author_name') );
add_shortcode( 'List_Paper_By_Year', array($tech_report, 'tech_reports_guest_view_paper_by_year') );

?>
