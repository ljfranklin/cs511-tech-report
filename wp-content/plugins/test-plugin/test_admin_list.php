<?php

if(!class_exists('WP_List_Table_Copy')){
	require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wp-list-table-copy.php' );
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
	$paper_id = $_GET['paper_id'];
	delete_paper($paper_id);
}

if (isset($_POST['action']) && $_POST['action'] == 'delete') {
	$paper_ids = $_POST['paper_id'];
	delete_multiple_papers($paper_ids);
}

function delete_paper($paper_id) {
	global $wpdb;

	$query = "SELECT wposts.ID
		FROM ".$wpdb->posts." AS wposts
		INNER JOIN ".$wpdb->postmeta." AS wpostmeta
		ON wpostmeta.post_id = wposts.ID
		AND wpostmeta.meta_key = 'paper_id'
		AND wpostmeta.meta_value = '$paper_id'";
		
	$post_id = $wpdb->get_var($query);
	wp_delete_post($post_id, true);
	
	unlink(get_paper_filename($paper_id));
	$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
	$paperDb->delete( 'paper', array( 'paper_id' => $paper_id ));	
}

function delete_multiple_papers($paper_ids) {
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
	
	$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
	foreach ($paper_ids as $paper_id){
		unlink(get_paper_filename($paper_id));
		$paperDb->delete( 'paper', array( 'paper_id' => $paper_id ));	
	}
}

class Link_List_Table extends WP_List_Table {

	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	 function __construct() {
		 parent::__construct( array(
		'singular'=> 'wp_list_text_link', //Singular label
		'plural' => 'wp_list_test_links', //plural label, also this well be one of the table css class
		'ajax'	=> false //We won't support Ajax for this table
		) );
	 }

	function get_columns() {
		return array(
			'cb' => '<input type="checkbox" />',
			'title'=>'Title',
			'author'=>'Author'
		);
	}
	
	function get_hidden_columns() {
		return array(
			'paper_id'
		);
	}
	
	function prepare_items() {
	
		$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
		$query = "SELECT * FROM paper";
		
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
  		$sortable = array();
  		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->items = $paperDb->get_results($query);
	}
	
	function column_default( $item, $column_name ) {
  		switch( $column_name ) { 
    		case 'paper_id':
    		case 'author':
    			return $item->$column_name;
    		default:
    	 		return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
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

$wp_list_table = new Link_List_Table();
$wp_list_table->prepare_items();
?>



<div class="wrap">
	<h2>Research Papers</h2>
	<form action="" method="POST">
		<?php $wp_list_table->display(); ?>
	</form>
</div>
