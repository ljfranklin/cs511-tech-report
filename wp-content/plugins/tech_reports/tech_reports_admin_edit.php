<?php

	$tech_report = new TechReports();

	$new_authors = isset($_POST['new_authors']) ? $_POST['new_authors'] : array();
   	$existing_authors = isset($_POST['existing_authors']) ? $_POST['existing_authors'] : array();
    if(isset($_POST['action']) && $_POST['action'] == 'create') {
    	$values = array(
    		'title' => $_POST['paper_title'],
    		'existing_authors' => $existing_authors,
    		'new_authors' => $new_authors,
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
    		'existing_authors' => $_POST['existing_authors'],
    		'new_authors' => $_POST['new_authors'],
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

<script>

var allAuthors = <?php echo $get_authors(); ?>;
var paperAuthors = <?php echo $get_paper_authors(); ?>;

$(document).ready(function() {

	addInputsForExistingAuthors();
	
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
	  source: substringMatcher(allAuthors),
	  templates: {
			empty: [
			  '<div class="empty-message add-new-author">',
			  'Add a new author',
			  '</div>'
    		].join('\n')
  		}
	});
	
	//don't submit form when enter is pressed in typeahead
	$typeahead.keydown(function(event){
		if(event.keyCode == 13) {
		  event.preventDefault();
		  return false;
		}
	});
		
	$typeahead.on('typeahead:selected', function(e, author) {
		e.preventDefault();
		addAuthor(author);
		$(this).typeahead('val', '');
	});
	$('.author-list').on('click', '.remove-author', removeAuthor);
	
	//don't submit if no authors added
	$('#paper-upload-form').on('submit', function(ev) {
		if ($('.author-inputs').size() === 0) {
			ev.preventDefault();
			$typeahead.focus();
			
			return;
		}
	});
});

function addInputsForExistingAuthors() {
	_.chain(allAuthors)
		.filter(function(author) {
			return _.findWhere(paperAuthors, {author_id: author.author_id});
		})
		.each(addAuthor);
}

function substringMatcher(authors) {

  return function findMatches(q, cb) {
	var matches, substringRegex;
	
	matches = [];
	substrRegex = new RegExp(q, 'i');
 	
	_.each(authors, function(author) {
	  var alreadyAdded = _.isUndefined(author.alreadyAdded) ? false : author.alreadyAdded;
	  if (alreadyAdded === false && substrRegex.test(author['full_name'])) {
	    matches.push(author);
	  }
	});
	
	//if no results, show option to add new author
	if (matches.length === 0) {
		matches.push({
			full_name: 'Add ' + q + ' as new author',
			isNewAuthor: true,
			rawName: q
		});
	}
 	
	cb(matches);
  };
};

function addAuthor(author) {

	author.alreadyAdded = true;

	var existingAuthorTemplate = _.template($('#existing-author-template').html());
	var newAuthorTemplate = _.template($('#new-author-template').html());
	var $authorList = $('.author-list');
		
	var elementContent;
	if (author.isNewAuthor) {
		var rawName = author.rawName.trim();
		var authorData = splitFullNameIntoFirstLast(rawName);
		authorData.newAuthorIndex = $authorList.find('.new-author').size();
	
		elementContent = newAuthorTemplate(authorData);
	} else {
		elementContent = existingAuthorTemplate(author);
	}
	
	var $authorElement = $(elementContent);
	$authorElement.data('author', author);
	
	$authorList.append($authorElement);
	
	$authorList.find('input[disabled], select[disabled]').prop('disabled', true);
	$authorList.find('select[disabled]').each(function(i, el) {
		var val = $(el).attr('value');
		$(el).val(val);
	});
}

function splitFullNameIntoFirstLast(fullName) {
	var authorData = {};
	
	var spaceIndex = fullName.indexOf(' ');
	if (spaceIndex <= 0) {
		authorData.first_name = fullName;
		authorData.last_name = '';
	} else {
		authorData.first_name = fullName.substring(0, spaceIndex);
		authorData.last_name = fullName.substring(spaceIndex + 1);
	}
	authorData.middle_name = '';
	
	return authorData;
}

function removeAuthor(event) {
	event.preventDefault();
		
	var $btn = $(event.target);
	var $authorElement = $btn.parents('.author-inputs');
	
	var author = $authorElement.data('author');
	author.alreadyAdded = false;
	
	var isNewAuthor = $authorElement.hasClass('new-author');
	
	var $authorList = $authorElement.parents('.author-list');
	$authorElement.remove();
	
	//Make sure whitespace is removed so No Authors message is displayed by css
	if ($authorList.children().size() === 0) {
		$authorList.html('');
	} 
	
	//update new author indexes
	if (isNewAuthor) {
		$authorList.find('.new-author').each(function(authorIndex, authorDiv) {
			var $author = $(authorDiv);
			$author.find('input').each(function(i, input) {
				var $input = $(input);
				var originalName = $input.attr('name');
				var newName = originalName.replace(/\[\d+\]/, '\[' + authorIndex + '\]');
				$input.attr('name', newName);
			});
		});
	}
}

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
