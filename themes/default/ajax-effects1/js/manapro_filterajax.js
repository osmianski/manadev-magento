/**
 * @category    Mana
 * @package     ManaPro_FilterAjax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
(function($) {
	$(document).bind('m-ajax-before', function(e, selectors) {
		selectors.each(function(selector, selectorIndex) {
			var left = 0, top = 0, right = 0, bottom = 0, assigned = false;
			$(selector).each(function() {
				var element = $(this);
				var elOffset = element.offset(), elWidth = element.width(), elHeight = element.height();
				if (!assigned || left > elOffset.left) { left = elOffset.left; }
				if (!assigned || top > elOffset.top) { top = elOffset.top; }
				if (!assigned || right < elOffset.left + elWidth) { right = elOffset.left + elWidth; }
				if (!assigned || bottom < elOffset.top + elHeight) { bottom = elOffset.top + elHeight; }
				assigned = true;
			});
			if (assigned) {
				// create overlay
				var overlay = $('<div class="m-overlay m-overlay-'+ selectorIndex +'"> </div>');
				overlay.appendTo(document.body);
				overlay.css({left: left, top: top}).width(right - left).height(bottom - top);
			}
		});
		
		if ($.options('#m-filter-ajax').progress) {
			$('#m-wait').show();
		}
	});
	$(document).bind('m-ajax-after', function(e, selectors) {
		// remove overlays
		$('.m-overlay').remove();
		if ($.options('#m-filter-ajax').progress) {
			$('#m-wait').hide();
		}
	});
})(jQuery);
