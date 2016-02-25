/**
 * @category    Mana
 * @package     ManaPro_FilterColors
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
    $(document).on('mouseover', '.m-filter-colors .m-color, .state.m-color', function() {
        $(this).addClass('hovered');
    });
    $(document).on('mouseout', '.m-filter-colors .m-color, .state.m-color', function() {
        $(this).removeClass('hovered');
    });
})(jQuery);
