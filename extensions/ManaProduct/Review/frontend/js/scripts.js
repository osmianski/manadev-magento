/**
 * @category    Mana
 * @package     ManaPro_ProductPlusProduct
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
    function _openTab(id) {
        var target = $(id);
        if (target.length) {
            var tab = target.parents('.ui-tabs-panel');
            if (tab.length) {
                $(target.parents('.ui-tabs')[0]).tabs('select', '#' + tab[0].id);
                return true;
            }
        }
        return false;
    }
    function _scrollToElement(id) {
        $('html,body').animate({
            scrollTop:'+=' + $(id).offset().top + 'px'
        }, 'fast');
    }
    $(function() {
        if (location.hash == '#review-form' || location.hash == '#review-list') {
            if (_openTab(location.hash)) {
                _scrollToElement(location.hash);
            }
        }
    });
    $('a.m-open-review-list,a.m-open-review-form').live('click', function() {
        var id = $(this).hasClass('m-open-review-list') ? '#review-list' : '#review-form';
        _openTab(id);
        _scrollToElement(id);
        return false;
    });
})(jQuery);
