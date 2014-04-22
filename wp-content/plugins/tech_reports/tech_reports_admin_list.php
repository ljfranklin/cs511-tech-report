<?php

if(!class_exists('WP_List_Table_Copy')){
	require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wp-list-table-copy.php' );
}

$tech_report = new TechReports();
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
	$paper_id = $_GET['paper_id'];
	$tech_report->delete_paper($paper_id);
}

if ((isset($_POST['action']) && $_POST['action'] == 'delete') || (isset($_POST['action2']) && $_POST['action2'] == 'delete')) {
	$paper_ids = $_POST['paper_id'];
	$tech_report->delete_multiple_papers($paper_ids);
}

class Paper_List_Table extends WP_List_Table {

	 function __construct() {
		 parent::__construct( array(
			'singular'=> 'wp_list_paper',
			'plural' => 'wp_list_papers',
			'ajax'	=> false
		));
	 }

	function get_columns() {
		return array(
			'cb' => '<input type="checkbox" />',
			'title'=>'Title',
			'authors' => 'Authors',
			'type' => 'Type',
			'published_at' => 'Journal/Conference',
			'publication_year' => 'Year'
		);
	}
	
	function get_hidden_columns() {
		return array(
			'paper_id'
		);
	}
	
	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
  		$sortable = array();
  		$this->_column_headers = array($columns, $hidden, $sortable);

		$current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
		$page_args = array(
			'current_page' => $current_page,
    		'per_page' => 20
    	);
    	
    	$tech_report = new TechReports();
    	
    	$tech_report->query_recent_papers($page_args);
    	
		$this->items = $tech_report->get_all_queried_papers();
    	
		$this->set_pagination_args(array(
			'total_items' => $tech_report->get_total_results(),                 
			'per_page'    => $page_args['per_page']
		));
	}
	
	function column_default( $item, $column_name ) {
  		switch( $column_name ) { 
    		case 'paper_id':
    		case 'type':
    		case 'published_at':
    		case 'publication_year':
    			return $item[$column_name];
    		case 'authors':
				$to_full_names = function($author) {
					return $author['full_name'];
				};
    			return implode(', ', array_map($to_full_names, $item[$column_name]));
    		default:
    	 		return print_r( $item, true ); 
  		}
	}
	
	function column_title($item) {
  		$actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&paper_id=%s">Edit</a>','upload-paper','edit',$item['paper_id']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&paper_id=%s">Delete</a>',$_REQUEST['page'],'delete',$item['paper_id'])
        );

		return sprintf('%1$s %2$s', $item['title'], $this->row_actions($actions, true));
	}
	
	function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="paper_id[]" value="%s" />', $item['paper_id']
        );    
    }
	
	function get_bulk_actions() {
  		$actions = array(
    		'delete'    => 'Delete'
  		);
  		return $actions;
	}
}

$paper_table = new Paper_List_Table();
$paper_table->prepare_items();
?>

<div class="wrap">
	<h2>Research Papers</h2>
	<form action="" method="POST">
		<?php $paper_table->display(); ?>
	</form>
</div>
