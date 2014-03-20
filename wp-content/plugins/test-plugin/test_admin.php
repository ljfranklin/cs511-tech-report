<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
	if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
	
	ob_start();

    if(isset($_POST['action']) && $_POST['action'] == 'create') {
    	$postId = add_new_paper();
		
		wp_redirect(get_site_url()."/?p=$postId");
		exit;
    } 
    if(isset($_POST['action']) && $_POST['action'] == 'edit') {
    	$postId = update_paper();
		
		wp_redirect(get_site_url()."/?p=$postId");
		exit;
    } 
    
    
    $is_editing = false;
    $existing_values = array();
    if (isset($_GET['paper_id']) && (isset($_GET['action']) && $_GET['action'] == 'edit')){
    	$paper_id = $_GET['paper_id'];
    	$is_editing = true;
    	
    	$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
		$query = "SELECT * FROM paper WHERE paper_id=$paper_id";
		
		$existing_values = $paperDb->get_row($query, ARRAY_A);
		
		$pdf_path = get_paper_filename($paper_id, $existing_values['title']);
		$base_path = ABSPATH;
		$existing_values['filename'] = "../" . substr($pdf_path, strlen($base_path));
    }
    
    $get_existing_value = function($name) use ($existing_values) {
    	if (array_key_exists($name, $existing_values)) {
    		return $existing_values[$name];
    	} else {
    		return "";
    	}
    };
    
    function add_new_paper() {
    	$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
    	
        $title = $_POST['paper_title'];
        $author = $_POST['paper_author'];
        $abstract = $_POST['paper_abstract'];
        
        $paperDb->insert( 
			'paper', 
			array( 
				'title' => $title,
				'author' => $author
			), 
			array( 
				'%s',
				'%s'
			) 
		);
		$paperId = $paperDb->insert_id;
		
		$userId = get_current_user_id();
		$newPost = array(
			'post_title' => $title,
			'post_content' => '',
			'post_status' => 'publish',
			'post_date' => date('Y-m-d H:i:s'),
			'post_author' => $userId,
			'post_type' => 'post',
			'post_category' => array(0)
		);
		$postId = wp_insert_post($newPost);
		add_post_meta($postId, 'paper_id', $paperId);
		
		process_file_upload($paperId, $title);
		
		return $postId;
    }
    
    function update_paper() {
	    global $wpdb;
    	$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
    	
    	$paper_id = $_POST['paper_id'];
        $title = $_POST['paper_title'];
        $author = $_POST['paper_author'];
        $abstract = $_POST['paper_abstract'];
        
        $paperDb->update( 
			'paper', 
			array( 
				'title' => $title,
				'author' => $author
			), 
			array(
				'paper_id' => $paper_id
			),
			array( 
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
		$postId = wp_update_post($updatedPost);
		
		//TODO: handle deleting/renaming pdf files
		process_file_upload($paper_id, $title);
		
		return $postId;
    }
    
    function process_file_upload($paper_id, $title) {
    
    	$uploadedfile = $_FILES['paper_upload'];
    	$finfo = new finfo(FILEINFO_MIME_TYPE);
    	if (false === array_search(
    	    $finfo->file($uploadedfile['tmp_name']),
    	    array(
    	        'pdf' => 'application/pdf'
    	    ),
    	    true
    	)) {
        	throw new RuntimeException('Invalid file format.');
    	}
    	
    	
		if (!move_uploaded_file(
        	$_FILES['paper_upload']['tmp_name'],
        	get_paper_filename($paper_id, $title)
    	)) {
    	    throw new RuntimeException('Failed to move uploaded file.');
    	}
    }
?>
<div class="wrap">
	<h2>Upload a Research Paper</h2>
	<form id="paper-upload-form" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>&noheader=true" enctype="multipart/form-data">
		<input type="hidden" name="action" value="<?php echo $is_editing ? 'edit' : 'create' ?>"/>
		<?php if ($is_editing) { ?>
			<input type="hidden" name="paper_id" value="<?php echo $get_existing_value('paper_id') ?>"/>
		<?php } ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th>
						<label for="paper_title">Paper Title:</label>
					</th>
					<td>
						<input type="text" id="paper_title" name="paper_title" size="30" value="<?php echo $get_existing_value('title') ?>" required/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="paper_author">Author:</label>
					</th>
					<td>
						<input type="text" id="paper_author" name="paper_author" size="30" value="<?php echo $get_existing_value('author') ?>" required/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="paper_abstract">Abstract:</label>
					</th>
					<td>
						<textarea id="paper_abstract" name="paper_abstract" rows="10" cols="30" value="<?php echo $get_existing_value('abstract') ?>" required></textarea>
					</td>
				</tr>
				<tr>
					<th>
						<label for="paper_upload">PDF Upload:</label>
					</th>
					<td>
						<?php if ($is_editing) { ?>
							<a href="<?php echo $get_existing_value('filename') ?>" target="_blank">Existing PDF</a><br/>
							Replace File:
						<?php } ?>
						<input type="file" name="paper_upload" id="paper_upload" accept="application/pdf" required/>
					</td>
				</tr>
			</tbody>
		</table>
		
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Submit Paper">
		</p>
	</form>
</div>
