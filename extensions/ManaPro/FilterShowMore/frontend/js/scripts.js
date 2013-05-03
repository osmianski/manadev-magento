/**
 * @category    Mana
 * @package     ManaPro_FilterShowMore
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
;(function($, undefined) {
	var prefix = 'm-more-less-';
	var _inAjax = false;
	var _states = {};
	var _itemCounts = {};
	var _time = {};
	var _popupUrls = {};
	var _popupTargetUrls = {};
    var _popupProgress = false;
    var _popupDebug = false;
    var _lastPopupCode = null;
    var _popupValues = {};
    var _lastPopupValues = null;
    var _popupAction = 'click';

	function _calculateHeights(l, code) {
	    var visible = l.is(':visible');
	    var hiddenElement = l;
	    if (!visible) {
            while (hiddenElement.parent().length && !hiddenElement.parent().is(':visible')) {
                hiddenElement = hiddenElement.parent();
            }
            hiddenElement.show();
	    }
		var heights = {less: 0, more: 0};
		l.children().each(function(index, item) {
            if (
                index < _itemCounts[code] ||
                !l.hasClass('m-reverse') && $(item).hasClass('m-selected-ln-item') ||
                l.hasClass('m-reverse') && !$(item).hasClass('m-selected-ln-item')
            ) {
                heights.less += $(item).outerHeight(true);
            }

			heights.more += $(item).outerHeight(true);
		});
		if (!visible) {
            hiddenElement.hide();
		}
		return heights;
	}
	function apply(code, withTransition) {
		var div = $('#'+prefix+code);
		var l = div.parent().children().first();
        var heights;

        l.addClass('m-expandable-filter');
		if (_states[code]) {
			l.children().each(function(index, item) {
				if (! (
				    index < _itemCounts[code] ||
                    !l.hasClass('m-reverse') && $(item).hasClass('m-selected-ln-item') ||
                    l.hasClass('m-reverse') && !$(item).hasClass('m-selected-ln-item')
				)) {
					$(item).show();
				}
            });

			heights = _calculateHeights(l, code);
			if (withTransition) {
				l.animate({height: heights.more+'px'}, _time[code]);
			}
			else {
				l.height(heights.more);
			}
			div.find('.m-show-less-action').show();
			div.find('.m-show-more-action').hide();
		}
		else {
			l.children().each(function(index, item) {
				if (! (
				    index < _itemCounts[code] ||
                    !l.hasClass('m-reverse') && $(item).hasClass('m-selected-ln-item') ||
                    l.hasClass('m-reverse') && !$(item).hasClass('m-selected-ln-item')
				)) {
                    $(item).hide();
				}
			});
			
			heights = _calculateHeights(l, code);
			if (withTransition) {
				l.animate({height: heights.less+'px'}, _time[code]);
			}
			else {
				l.height(heights.less);
			}
			div.find('.m-show-less-action').hide();
			div.find('.m-show-more-action').show();
		}
	}

    function getFilterCode(el) {
        var code = $(el).parent()[0].id;
        if (!code.match("^" + prefix) == prefix) {
            throw 'Unexpected show more/show less id';
        }
        return code.substring(prefix.length);
    }
	function clickHandler() {
		var code = getFilterCode(this);
		_states[code] = !_states[code];
		apply(code, true);
		return false;
	}
	
	$(document).bind('m-show-more-reset', function(e, code, itemCount, showAll, time) {
		if (!_inAjax){
			_states[code] = showAll;
		}
		_itemCounts[code] = itemCount;
		_time[code] = time;
		apply(code, false);
	});
    $(document).bind('m-filter-scroll-reset', function (e, code, itemCount) {
        _itemCounts[code] = itemCount;
        var div = $('#' + prefix + code);
        var l = div.parent().children().first();

        l.addClass('m-scrollable-filter');
        var heights = _calculateHeights(l, code);
        l.height(heights.less);
    });
    $(document).bind('m-show-more-popup-reset', function (e, code, url, targetUrl, values, action, showWait, debug) {
        _popupUrls[code] = $.base64_decode(url);
        _popupTargetUrls[code] = $.base64_decode(targetUrl);
        _popupProgress = showWait;
        _popupDebug = debug;
        _popupValues[code] = values ? values.split('_') : [];
        _popupAction = action;
        _bindPopupActions();
    });

    $(document).bind('m-ajax-before', function(e, selectors) {
		_inAjax = true;
	});
	$(document).bind('m-ajax-after', function(e, selectors) {
		for (var code in _states) {
			apply(code, false);
		}
		_inAjax = false;
	});
	$('a.m-show-less-action').live('click', clickHandler);
	$('a.m-show-more-action').live('click', clickHandler);

    var _popupActionsBound = false;
    function _bindPopupActions() {
        if (!_popupActionsBound) {
            _popupActionsBound = true;
            if (_popupAction == 'mouseover') {
                $('a.m-show-more-popup-action').live('mouseover', _popupClick);
            }
            else {
                $('a.m-show-more-popup-action').live('click', _popupClick);
            }
        }
    }
	function _popupClick() {
        if (_popupProgress) {
            $('#m-wait').show();
        }
        var code = getFilterCode(this);
        _lastPopupCode = code;
        _lastPopupValues = _popupValues[code].slice(0);
        $.get(_popupUrls[code])
            .done(function (response) {
                try {
                    if (!response) {
                        if (_popupDebug) {
                            alert('No response.');
                        }
                    }
                    else {
                        $.mSetPopupFadeoutOptions({ overlayTime:0, popupTime:500, callback: null });
                        var overlay = $('<div class="m-popup-overlay"> </div>');
                        overlay.appendTo(document.body);
                        overlay.css({left:0, top:0}).width($(document).width()).height($(document).height());
                        overlay.animate({ opacity:0.2 }, 0, function () {
                            $('#m-popup')
                                .css({"width": "auto", "height": "auto"})
                                .html(response)
                                .addClass('m-showmore-popup-container')
                                .show();
                                
                            $('#m-popup').find('.m-rows').each(function () {
                                var rows = $(this);
                                var maxRowCount = rows.attr('data-max-rows');
                                var height = 0;
                                if (maxRowCount) {
                                    rows.children().each(function (index) {
                                        if (index < maxRowCount) {
                                            height += $(this).outerHeight();
                                        }
                                    });
                                    rows.width(rows.width() + 30).height(height).addClass('m-scrollable-filter');
                                }
                            });
                                
                            $('#m-popup')
                                .css("top", (($(window).height() - $('#m-popup').outerHeight()) / 2) + $(window).scrollTop() + "px")
                                .css("left", (($(window).width() - $('#m-popup').outerWidth()) / 2) + $(window).scrollLeft() + "px")

                            var popupHeight = $('#m-popup').height();
                            $('#m-popup').hide().css({"height": "auto"});

                            var css = {
                                left:$('#m-popup').css('left'),
                                top:$('#m-popup').css('top'),
                                width:$('#m-popup').width() + "px",
                                height:$('#m-popup').height() + "px"
                            };

                            $('#m-popup').children().each(function () {
                                $(this).css({
                                    width:($('#m-popup').width() + $(this).width() - $(this).outerWidth()) + "px",
                                    height:($('#m-popup').height() + $(this).height() - $(this).outerHeight()) + "px"
                                });
                            });
                            $('#m-popup')
                                .css({
                                    top:($(window).height() / 2) + $(window).scrollTop() + "px",
                                    left:($(window).width() / 2) + $(window).scrollLeft() + "px",
                                    width:0 + "px",
                                    height:0 + "px"
                                })
                                .show();

                            $('#m-popup').animate(css, 300);
                        });
                    }
                }
                catch (error) {
                    if (_popupDebug) {
                        alert(error);
                    }
                }
            })
            .fail(function (error) {
                if (_popupDebug) {
                    alert(error.status + (error.responseText ? ': ' + error.responseText : ''));
                }
            })
            .complete(function () {
                if (_popupProgress) {
                    $('#m-wait').hide();
                }
            });

        return false;
	}

    function _applyPopup(values) {
        var code = _lastPopupCode;
        _lastPopupCode = null;
        $('.m-popup-overlay').fadeOut(500, function () {
            $('.m-popup-overlay').remove();
            $('#m-popup').fadeOut(1000);
        });

        var param = values.join('_');
        setLocation(_popupTargetUrls[code].replace('__0__', param));
    }
    $.mShowMorePopupApply = function (value) {
        if (value === undefined) {
            _applyPopup(_lastPopupValues);
        }
        else {
            _applyPopup([value]);
        }
        return false;
    };
    $.mShowMorePopupSelect = function(value, isSelected) {
        var index = $.inArray(value, _lastPopupValues);
        if (isSelected) {
            if (index == -1) {
                _lastPopupValues.push(value);
            }
        }
        else {
            if (index != -1) {
                _lastPopupValues.splice(index, 1);
            }
        }
        return false;
    };
})(jQuery);
