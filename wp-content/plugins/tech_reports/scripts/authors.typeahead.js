
var AuthorsTypeahead = function(opts) {

	var $typeahead = opts.$el;
	var $authorList = opts.$authorList;
	var existingAuthorHtml = opts.existingAuthorTemplate;
	var newAuthorHtml = opts.newAuthorTemplate;
	var allAuthors = opts.allAuthors;
	var paperAuthors = opts.paperAuthors;

	addInputsForExistingAuthors();
	
	$typeahead.typeahead({
	  hint: true,
	  highlight: true,
	  minLength: 1
	},
	{
	  name: 'authors',
	  displayKey: 'full_name',
	  valueKey: 'author_id',
	  source: substringMatcher(allAuthors)
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
	$authorList.on('click', '.remove-author', removeAuthor);
	
	this.count = function() {
		return $authorList.find('.author-inputs').size();
	};

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

		var existingAuthorTemplate = _.template(existingAuthorHtml);
		var newAuthorTemplate = _.template(newAuthorHtml);
		
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
};