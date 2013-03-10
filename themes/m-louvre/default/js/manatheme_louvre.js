/**
 * @category    Mana
 * @package     ManaTheme_Louvre
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
    $(function () {
        Mana.Theme.beautifySelects();
/*

        $('.col-right.sidebar').insertBefore($('.col-wrapper'));
        $('.col-left.sidebar').insertBefore($('.col-main'));
*/
        Mana.Theme.fluidLayout();
    });
})(jQuery);
