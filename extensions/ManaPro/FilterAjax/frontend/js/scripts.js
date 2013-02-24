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
;(function($) {
    $(function() {
        $.mInterceptUrls('filter', $.extend({}, $.options('#m-filter-ajax'), {
            // called when filter URL is about to be rendered
            callback: function(url, element, action, selectors) {
                var productsClicked = element !== undefined && (
                    $(element).parents().hasClass('mb-category-products') ||
                    $(element).parents().hasClass('mb-cms-products')
                );
                $.mGetBlocksHtml(url, action, selectors, function(response) {
                    $.mUpdateBlocksHtml(response);

                    if (productsClicked && $.options('#m-filter-ajax').scroll) {
                        var offset = -1;
                        $.each(selectors, function (index, selector) {
                            var jq = $(selector);
                            if (jq.length && (offset < 0 || offset > jq.offset().top)) {
                                offset = jq.offset().top;
                            }
                        });
                        if (offset >= 0) {
                            offset -= 10;
                            if (offset < 0) {
                                offset = 0;
                            }
                            scroll(0, offset);
                        }
                    }
                });
            }
        }));
    });
})(jQuery);
