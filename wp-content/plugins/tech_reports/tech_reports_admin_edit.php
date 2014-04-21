<?php

	$tech_report = new TechReports();
   	
    if(isset($_POST['action']) && $_POST['action'] == 'create') {
    	$values = get_values();
    	$paper_id = $tech_report->add_new_paper($values);
	
		wp_redirect(get_site_url()."/?paper=$paper_id");
		exit;
    } 
    if(isset($_POST['action']) && $_POST['action'] == 'edit') {
    	$values = get_values();
    	$values['paper_id'] = $_POST['paper_id'];
    	$paper_id = $tech_report->update_paper($values, $_POST['previous_year']);
		
		wp_redirect(get_site_url()."/?paper=$paper_id");
		exit;
    }
    
	if (isset($_GET['paper_id']) && (isset($_GET['action']) && $_GET['action'] == 'edit')) {
		$paper = $tech_report->get_paper($_GET['paper_id']);
		$is_editing = true;
	} else {
		$paper = array();
		$is_editing = false;
	}
	
	function get_values() {
	
		$new_authors = isset($_POST['new_authors']) ? $_POST['new_authors'] : array();
   		$existing_authors = isset($_POST['existing_authors']) ? $_POST['existing_authors'] : array();
	
		$type = $_POST['paper_type'];
    	if ($type === 'journal') {
    		$published_at = trim($_POST['paper_journal']);
    	} else if ($type === 'conference') {
    		$published_at = trim($_POST['paper_conference']);
    	} else {
    		$published_at = NULL;
    	}
    	
    	return array(
    		'title' => $_POST['paper_title'],
    		'existing_authors' => $existing_authors,
    		'new_authors' => $new_authors,
    		'abstract' => $_POST['paper_abstract'],
    		'publication_year' => $_POST['paper_year'],
    		'type' => $type,
    		'published_at' => $published_at,
    		'keywords' => $_POST['paper_keywords'],
    		'file' => $_FILES['paper_upload']
    	);
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
    
    $get_paper_authors = function() use ($paper) {
    	$authors = isset($paper['authors']) ? $paper['authors'] : array();
    	return json_encode($authors);
    };
?>

<?php $plugin_url = plugin_dir_url( __FILE__ ); ?>

<link href="<?php echo $plugin_url; ?>scripts/typeahead.css" rel="stylesheet" type="text/css">
<link href="<?php echo $plugin_url; ?>styles/styles.css" rel="stylesheet" type="text/css">

<script src="<?php echo $plugin_url; ?>scripts/jquery-2.1.0.min.js"></script>
<script src="<?php echo $plugin_url; ?>scripts/underscore-min.js"></script>
<script src="<?php echo $plugin_url; ?>scripts/typeahead.bundle.min.js"></script>
<script id="existing-author-template" type="text/template">
   <div class="author-inputs existing-author">
       <input type="hidden" name="existing_authors[]" value="<%= author_id %>">
   	   <input type="text" value="<%= first_name %>" disabled>
       <input type="text" value="<%= middle_name %>" disabled>
       <input type="text" value="<%= last_name %>" disabled>
       <select value="<%= suffix %>" disabled>
           <option value=""></option>
           <option value="jr">Jr</option>
           <option value="sr">Sr</option>
           <option value="ii">II</option>
           <option value="iii">III</option>
           <option value="iv">IV</option>
       </select>
       <button class="remove-author">X</button>
   </div>
</script>
<script id="new-author-template" type="text/template">
   <div class="author-inputs new-author">
       <input type="text" name="new_authors[<%= newAuthorIndex %>][first_name]" value="<%= first_name %>" placeholder="First name" required>
       <input type="text" name="new_authors[<%= newAuthorIndex %>][middle_name]" value="<%= middle_name %>" placeholder="Middle name (optional)">
       <input type="text" name="new_authors[<%= newAuthorIndex %>][last_name]" value="<%= last_name %>" placeholder="Last name" required>
       <select name="new_authors[<%= newAuthorIndex %>][suffix]">
           <option value=""></option>
           <option value="jr">Jr</option>
           <option value="sr">Sr</option>
           <option value="ii">II</option>
           <option value="iii">III</option>
           <option value="iv">IV</option>
       </select>
       <button class="remove-author">X</button>
   </div>
</script>
<script src="<?php echo $plugin_url; ?>scripts/authors.typeahead.js"></script>

<script>

var allAuthors = <?php echo $get_authors(); ?>;
var paperAuthors = <?php echo $get_paper_authors(); ?>;

$(document).ready(function() {
	var authors = new AuthorsTypeahead({
		$el: $('.typeahead'),
		$authorList: $('.author-list'),
		existingAuthorTemplate: $('#existing-author-template').html(),
		newAuthorTemplate: $('#new-author-template').html(),
		allAuthors: allAuthors,
		paperAuthors: paperAuthors
	});
	
	//don't submit if no authors added
	$('#paper-upload-form').on('submit', function(ev) {
		if (authors.count() === 0) {
			ev.preventDefault();
			$('.typeahead').focus();
			
			return;
		}
	});
	
	$('#paper_type').change(updateJournalConferenceDisplay).change();
});

function updateJournalConferenceDisplay() {
	var selectedType = $(this).val();
	var $journalInput = $('.journal_name');
	var $conferenceInput = $('.conference_name');
	
	$journalInput.hide();
	$conferenceInput.hide();
	$journalInput.find('input').prop('required', false);
	$conferenceInput.find('input').prop('required', false);
	if (selectedType === 'journal') {
		$journalInput.show();
		$journalInput.find('input').prop('required', true);
	} else if (selectedType === 'conference') {
		$conferenceInput.show();
		$conferenceInput.find('input').prop('required', true);
	}
}


</script>

<div class="wrap">
	<h2>Upload a Research Paper</h2>
	<form id="paper-upload-form" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>&noheader=true" enctype="multipart/form-data">
		<input type="hidden" name="action" value="<?php echo $is_editing ? 'edit' : 'create' ?>"/>
		<?php if ($is_editing) { ?>
			<input type="hidden" name="paper_id" value="<?php echo $get_existing_value('paper_id') ?>"/>
			<input type="hidden" name="previous_year" value="<?php echo $get_existing_value('publication_year') ?>"/>
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
						<label for="paper_author">Author(s):</label>
					</th>
					<td>
						<div class="typeahead-container scrollable-dropdown-menu has-empty-option">
							<input class="typeahead" type="text" placeholder="Search Author Names">
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
							<option value="">Select Paper Type</option>
							<option value="tech-report" <?php if ($get_existing_value('type') === "tech-report") echo "selected=\"selected\"" ?>>Technical Report</option>
							<option value="journal" <?php if ($get_existing_value('type') === "journal") echo "selected=\"selected\"" ?>>Journal Publication</option>
							<option value="conference" <?php if ($get_existing_value('type') === "conference") echo "selected=\"selected\"" ?>>Conference Publication</option>
							<option value="phd" <?php if ($get_existing_value('type') === "phd") echo "selected=\"selected\"" ?>>PhD Dissertation</option>
						</select>
					</td>
				</tr>
				<tr class="journal_name" style="display: none">
 					<th>
 						<label for="paper_journal">Journal Name:</label>
 					</th>
 					<td>
 						<input id="paper_journal" type="text" name="paper_journal" value="<?php echo $get_existing_value('published_at') ?>" placeholder="Journal Name">
 					</td>
 				</tr>
 				<tr class="conference_name" style="display: none">
 					<th>
 						<label for="paper_conference">Conference Name:</label>
 					</th>
 					<td>
 						<input id="paper_conference" type="text" name="paper_conference" value="<?php echo $get_existing_value('published_at') ?>" placeholder="Conference Name">
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
						<label for="paper_keywords">Keywords (comma separated):</label>
					</th>
					<td>
						<input id="paper_keywords" name="paper_keywords" size="30" value="<?php echo $get_existing_value('keywords') ?>" placeholder="Enter keywords [optional]">
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
