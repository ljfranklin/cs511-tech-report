<?php

	$tech_report = new TechReports();

    if(isset($_POST['action']) && $_POST['action'] == 'create') {
    	
    	$values = array(
    		'title' => $_POST['paper_title'],
    		'authors' => $_POST['existing-authors'],
    		'abstract' => $_POST['paper_abstract'],
    		'year' => $_POST['paper_year'],
    		'type' => $_POST['paper_type'],
    		'file' => $_FILES['paper_upload']
    	);	
    	$post_id = $tech_report->add_new_paper($values);
	
		wp_redirect(get_site_url()."/?p=$post_id");
		exit;
    } 
    if(isset($_POST['action']) && $_POST['action'] == 'edit') {
    	$values = array(
    		'paper_id' => $_POST['paper_id'],
    		'title' => $_POST['paper_title'],
    		'authors' => $_POST['existing-authors'],
    		'abstract' => $_POST['paper_abstract'],
    		'year' => $_POST['paper_year'],
    		'type' => $_POST['paper_type'],
    		'file' => $_FILES['paper_upload']
    	);
    	$post_id = $tech_report->update_paper($values, $_POST['previous_title']);
		
		wp_redirect(get_site_url()."/?p=$post_id");
		exit;
    }
    
	if (isset($_GET['paper_id']) && (isset($_GET['action']) && $_GET['action'] == 'edit')) {
		$paper = $tech_report->get_paper($_GET['paper_id']);
		$is_editing = true;
	} else {
		$paper = array();
		$is_editing = false;
	}
	
	$get_existing_value = function($name) use ($paper) {
    	if (array_key_exists($name, $paper)) {
    		return $paper[$name];
    	} else {
    		return "";
    	}
    };
    
    $get_authors = function() use ($tech_report) {
    	$authors = $tech_report->get_all_authors();
    	return json_encode($authors);
    };
?>

<?php $plugin_url = plugin_dir_url( __FILE__ ); ?>

<link href="<?php echo $plugin_url; ?>scripts/typeahead.css" rel="stylesheet" type="text/css">

<script src="<?php echo $plugin_url; ?>scripts/jquery-2.1.0.min.js"></script>
<script src="<?php echo $plugin_url; ?>scripts/underscore-min.js"></script>
<script src="<?php echo $plugin_url; ?>scripts/typeahead.bundle.js"></script>
<script id="existing-author-template" type="text/template">
   <div class="existing-author">
       <div class="author-name">
	       <%= name %>
	   </div>
	   <input type="hidden" name="existing-authors[]" value="<%= authorId %>">
   </div>
</script>

<script>

var authors = <?php echo $get_authors(); ?>;

$(document).ready(function() {

	formatAuthors();

	var substringMatcher = function(authors) {
	
	  return function findMatches(q, cb) {
		var matches, substringRegex;
		
		matches = [];
		substrRegex = new RegExp(q, 'i');
	 
		$.each(authors, function(i, author) {
		  if (substrRegex.test(author['full_name'])) {
		    matches.push(author);
		  }
		});
	 	
		cb(matches);
	  };
	};
	
	var $typeahead = $('.typeahead');
	
	$typeahead.typeahead({
	  hint: true,
	  highlight: true,
	  minLength: 1
	},
	{
	  name: 'authors',
	  displayKey: 'full_name',
	  valueKey: 'author_id',
	  source: substringMatcher(authors),
	  templates: {
			empty: [
			  '<div class="empty-message add-new-author">',
			  'Add a new author',
			  '</div>'
    		].join('\n')
  		}
	});
	
	var templateString = $('#existing-author-template').html();
	var existingAuthorTemplate = _.template(templateString);	
		
	var $authorList = $('.author-list');
	$typeahead.on('typeahead:selected', function(e, author) {

		$typeahead.typeahead('val', '');
		
		var existingAuthor = existingAuthorTemplate({
			name: author['full_name'],
			authorId: author['author_id']
		});
		
		$authorList.append(existingAuthor);
	});
	
	$('.typeahead-container').on('click', '.add-new-author', function() {
		var selectedVal = $typeahead.val();
		$typeahead.typeahead('val', '');
		$authorList.append(selectedVal);
	});
	
	function formatAuthors() {
		authors = _.each(authors, function(author) {
			author['full_name'] = [author['first_name'], author['middle_name'], author['last_name']].join(' ');
		});
	}
});

</script>

<div class="wrap">
	<h2>Upload a Research Paper</h2>
	<form id="paper-upload-form" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>&noheader=true" enctype="multipart/form-data">
		<input type="hidden" name="action" value="<?php echo $is_editing ? 'edit' : 'create' ?>"/>
		<?php if ($is_editing) { ?>
			<input type="hidden" name="paper_id" value="<?php echo $get_existing_value('paper_id') ?>"/>
			<input type="hidden" name="previous_title" value="<?php echo $get_existing_value('title') ?>"/>
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
						<div class="typeahead-container scrollable-dropdown-menu has-empty-option">
							<input class="typeahead" type="text" placeholder="Search Author Names">
						</div>
						<div>
							Author(s):
						</div>
						<div class="author-list"></div>
					</td>
				</tr>
				<tr>
					<th>
						<label for="paper_year">Publication Year (YYYY):</label>
					</th>
					<td>
						<input type="text" id="paper_year" name="paper_year" size="4" value="<?php echo $get_existing_value('publication_year') ?>" pattern="\d{4}" required/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="paper_type">Type:</label>
					</th>
					<td>
						<select id="paper_type" name="paper_type" required>
							<option value="tech-report" <?php if ($get_existing_value('type') === "tech-report") echo "selected=\"selected\"" ?>>Technical Report</option>
							<option value="journal" <?php if ($get_existing_value('type') === "journal") echo "selected=\"selected\"" ?>>Journal Publication</option>
							<option value="conference" <?php if ($get_existing_value('type') === "conference") echo "selected=\"selected\"" ?>>Conference Publication</option>
							<option value="phd" <?php if ($get_existing_value('type') === "phd") echo "selected=\"selected\"" ?>>PhD Dissertation</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>
						<label for="paper_abstract">Abstract:</label>
					</th>
					<td>
						<textarea id="paper_abstract" name="paper_abstract" rows="10" cols="30" required><?php echo $get_existing_value('abstract') ?></textarea>
					</td>
				</tr>
				<tr>
					<th>
						<label for="paper_upload">PDF Upload:</label>
					</th>
					<td>
						<?php if ($is_editing) { ?>
							<a href="<?php echo $get_existing_value('file') ?>" target="_blank">Existing PDF</a><br/>
							Replace File:
						<?php } ?>
						<input type="file" name="paper_upload" id="paper_upload" accept="application/pdf" <?php if ($is_editing == false) echo "required" ?>/>
					</td>
				</tr>
			</tbody>
		</table>
		
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Submit Paper">
		</p>
	</form>
</div>
