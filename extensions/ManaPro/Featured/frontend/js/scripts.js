/**
 * @category    Mana
 * @package     ManaPro_Featured
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
    var _options = {};

    $(document).bind('m-featured-carousel-reset', function (e, options) {
        if ($('.m-featured .m-carousel ol>li').length > 1) {
            var totalWidth = 0;
            var width = 0;
            var height = 0;
            $('.m-featured .m-carousel ol>li').each(function(index, item) {
                $(this)
                    .width($('.m-featured .m-carousel').innerWidth() - $(this).outerWidth() + $(this).width())
                    .height($('.m-featured .m-carousel').innerHeight() - $(this).outerHeight() + $(this).height());
                totalWidth += $(this).outerWidth();
                width = $(this).outerWidth();
                height = $(this).outerHeight();
            });
            $('.m-featured .m-carousel ol').width(totalWidth).height(height);

            _options = options;
            if (_options.hideEffect == 'slide') {
                $('.m-featured .m-carousel ol>li').show();
                _options = $.extend({}, { hideEffectOptions: {slideBy: width}}, _options);
            }
            $('.m-featured .m-carousel ol').mAdvListRotator($.extend(/*{debug: true}, */_options));
        }
        else {
            $('.m-featured .m-carousel ol>li').show();
        }
    });
})(jQuery);
