(function() {
	
	jQuery(document).ready(function() {
		
		jQuery('.search-field').attr('placeholder', 'Search by title, author, abstract text, or keyword');
		
		jQuery('.paper_expand.paper_display .paper_title').click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
		
			var $paperExpand = jQuery(this).parent('.paper_expand');
			
			var $paperBody = $paperExpand.find('.paper_body').first();
			$paperBody.stop().slideToggle();
			
			$paperExpand.toggleClass('expanded');
		});
	});
})();
