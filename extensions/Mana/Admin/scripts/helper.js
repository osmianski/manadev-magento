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
	var HELPER_HIDDEN = 0;
	var HELPER_SHOWING = 1;
	var HELPER_VISIBLE = 2;
	var HELPER_HIDING = 3;
	var _host = null;
	var _status = HELPER_HIDDEN;
	var _timer = null;
	var _over = false;
	var _forcedHide = false;

	var _options = {
		showTime: 100, // ms
		hideTime: 100 // ms
	};

	function _helperOver() {
		_status = HELPER_VISIBLE;
		_cancelShow(false);
	}
	function _helperOut() {
	    if (_status == HELPER_VISIBLE) {
            _status = HELPER_HIDING;
            _scheduledShow(false);
        }
	}
	function _show() {
		_hostIs(HELPER_VISIBLE);
		var helper = $(_host.helper);
		helper.show();
        _host.onShow(_host.host, _host.helper);
		$.mStickTo(_host.host, helper);
		helper.bind('mouseover', _helperOver).bind('mouseout', _helperOut);
		$(_host.host).bind('mouseover', _helperOver).bind('mouseout', _helperOut).addClass('m-helper-host');
	}
	function _hide() {
		_hostIs(HELPER_VISIBLE);
		var helper = $(_host.helper);
		helper.unbind('mouseover', _helperOver).unbind('mouseout', _helperOut);
		$(_host.host).unbind('mouseover', _helperOver).unbind('mouseout', _helperOut).removeClass('m-helper-host');
		_cancelShow(false);
		if (_forcedHide) {
            _forcedHide = false;
		}
		else {
            _host.onHide(_host.host, _host.helper);
        }
		helper.hide();
	}
	function _delayedShow() {
		_hostIs(HELPER_SHOWING);
		_status = HELPER_VISIBLE;
		_cancelShow(true);
		_show();
	}
	function _delayedHide() {
		_hostIs(HELPER_HIDING);
		_status = HELPER_HIDDEN;
		_cancelShow(true);
		_status = HELPER_VISIBLE;
		_hide();
		_status = HELPER_HIDDEN;
		_host = null;
	}
	function _hostIs(status) {
		if (status === null && _host || status !== null && status != _status) {
			throw 'Unexpected status';
		}
	}
	function _cancelShow(throwIfNull) {
		if (_timer) {
			clearTimeout(_timer);
			_timer = null;
		}
		else if (throwIfNull) {
			throw 'Ticking timer expected';
		}
	}
	function _scheduledShow(visible) {
		if (!_timer) {
			if (visible) {
				_timer = setTimeout(_delayedShow, _options.showTime);				
			}
			else {
				_timer = setTimeout(_delayedHide, _options.hideTime);				
			}
		}
		else {
			throw 'No ticking timer expected';
		}
	}
	$.hideHelperPopup = function(forced) {
	    if (forced) {
	        _forcedHide = true;
	    }
        _helperOut();
	};

	$.helperPopup = function(options) {
		if (!options.host || !options.helper) {
			throw 'Invalid arguments';
		}
		options = $.extend({
			onShow: function() {},
			onHide: function() {}
		}, options);
		
		switch (_status) {
			case HELPER_HIDDEN:
				_hostIs(null);
				_host = options;
				_status = HELPER_SHOWING;
				_scheduledShow(true);
				break;
			case HELPER_SHOWING:
				if (_host.host != options.host) {
					_cancelShow(true);
					_host = options;
					_scheduledShow(true);
				}
				break;
			case HELPER_VISIBLE:
				if (_host.host != options.host) {
					_hide();
					_host = options;
					_show();
				}
				break;
			case HELPER_HIDING:
				_status = HELPER_VISIBLE;
				_cancelShow(false);
				if (_host.host != options.host) {
					_hide();
					_host = options;
					_show();
				}
				break;
		}
	}
})(jQuery);
