<?php

if(!class_exists('WP_List_Table_Copy')){
	require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wp-list-table-copy.php' );
}

$tech_report = new TechReports();
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
	$paper_id = $_GET['paper_id'];
	$tech_report->delete_paper($paper_id);
}

if (isset($_POST['action']) && $_POST['action'] == 'delete') {
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
			'type' => 'Type',
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

		$tech_report = new TechReports();
		$this->items = $tech_report->get_all_papers();
	}
	
	function column_default( $item, $column_name ) {
  		switch( $column_name ) { 
    		case 'paper_id':
    		case 'type':
    		case 'publication_year':
    			return $item->$column_name;
    		default:
    	 		return print_r( $item, true ); 
  		}
	}
	
	function column_title($item) {
  		$actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&paper_id=%s">Edit</a>','upload-paper','edit',$item->paper_id),
            'delete'    => sprintf('<a href="?page=%s&action=%s&paper_id=%s">Delete</a>',$_REQUEST['page'],'delete',$item->paper_id)
        );

		return sprintf('%1$s %2$s', $item->title, $this->row_actions($actions, true));
	}
	
	function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="paper_id[]" value="%s" />', $item->paper_id
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
