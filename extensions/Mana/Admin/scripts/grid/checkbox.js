/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
;(function($) {
	var _helper = null;
	
	function _onHelperShow(td, helper) {
		$(helper).find('input.m-default').mMarkAttr('checked', $.gridData(td, 'is_default'));
		$(helper).find('input.m-default').mMarkAttr('disabled', $.gridData(td, 'is_default_disabled'));
		_helper = helper;
		// show/hide here in more complex helpers
	}
	function _onHelperHide(td, helper) {
		$.gridData(td, {is_default: $(helper).find('input.m-default').is(':checked')});
		_helper = null;
	}
	// the following function is executed when DOM ir ready. If not use this wrapper, code inside could fail if
	// executed when referenced DOM elements are still being loaded.
	$(function() {
		$(document).on('mouseover', '.ct-checkbox', function() {
			if ($.gridData(this, 'show_helper')) { 
				$.helperPopup({
					host: this, 
					helper: '#m-column-helper', 
					onShow: _onHelperShow,
					onHide: _onHelperHide
				});
			}
		});
		$(document).on('change', '.ct-checkbox input', function() {
			var td = $(this).parent('td')[0];
			$.gridData(td, {value :$(this).is(':checked') ? 1 : 0, is_default: false});
			if (_helper) {
				$(_helper).find('input.m-default').mMarkAttr('checked', $.gridData(td, 'is_default'));
			}
		});
	});
})(jQuery);
