/**
 * @category    Mana
 * @package     ManaPro_Slider
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
    $.fn.mSlider = function(options) {
        var slider = this;
        $(function() {
            var visibleContent = slider.children('.m-visible-content');
            var content = visibleContent.children('.m-content');

            var totalWidth = 0;
            var width = 0;
            var height = 0;
            content.children().each(function (index) {
                var item = $(this);
                item
                    .width(visibleContent.innerWidth() - item.outerWidth() + item.width())
                    .height(visibleContent.innerHeight() - item.outerHeight() + item.height());
                totalWidth += item.outerWidth();
                width = item.outerWidth();
                height = item.outerHeight();
            });
            content.width(totalWidth).height(height);

            if (content.children().length > 1) {
                if (options.hideEffect == 'slide') {
                    content.children().show();
                    options = $.extend({}, { hideEffectOptions:{slideBy:width}}, options);
                }
                content.mAdvListRotator($.extend({/*debug: true*/}, options));
            }
            else {
                content.children().show();
            }
        });
    };

})(jQuery);
