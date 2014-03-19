<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
	if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
	
	ob_start();

    if(isset($_POST['test_hidden']) && $_POST['test_hidden'] == 'Y') {
    	
    	$paperDb = new wpdb("wordpress", "wp1234", "tech_papers", "localhost");
    	
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
    	
    	$plugin_dir = plugin_dir_path( __FILE__ );
		if (!move_uploaded_file(
        	$_FILES['paper_upload']['tmp_name'],
        	sprintf('%suploads/%s.pdf',
        		$plugin_dir,
        	    sha1_file($_FILES['paper_upload']['tmp_name'])
        	)
    	)) {
    	    throw new RuntimeException('Failed to move uploaded file.');
    	}
    	
        
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
		
		wp_redirect(get_site_url()."/?p=$postId");
		exit;
    } 
?>
<div class="wrap">     
	<h2>Upload a Paper</h2>
	<form id="paper-upload-form" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>&noheader=true" enctype="multipart/form-data">
		<input type="hidden" name="test_hidden" value="Y"/>
		<table class="form-table">
			<tbody>
				<tr>
					<th>
						<label for="paper_title">Paper Title:</label>
					</th>
					<td>
						<input type="text" id="paper_title" name="paper_title" size="30" required/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="paper_author">Author:</label>
					</th>
					<td>
						<input type="text" id="paper_author" name="paper_author" size="30" required/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="paper_abstract">Abstract:</label>
					</th>
					<td>
						<textarea id="paper_abstract" name="paper_abstract" rows="10" cols="30" required></textarea>
					</td>
				</tr>
				<tr>
					<th>
						<label for="paper_upload">PDF Upload:</label>
					</th>
					<td>
						<input type="file" name="paper_upload" id="paper_upload" required/>
					</td>
				</tr>
			</tbody>
		</table>
		
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Submit Paper">
		</p>
	</form>
</div>
