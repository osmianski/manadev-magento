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
    //region URL interception API

    var _ajaxifiedUrlOptions = {};
    var _lastAjaxActionSource = null;
    var _urlAjaxActions = {};
    $.mInterceptUrls = function(action, options) {
        _ajaxifiedUrlOptions[action] = $.extend({
            exactUrls: {},
            partialUrls: {},
            urlExceptions: {},
            callback: function(url, element, action, selectors) {}
        }, options);
    };
    $.mStopInterceptingUrls = function(action) {
        delete _ajaxifiedUrlOptions[action];
    };

    function _urlAjaxAction(locationUrl) {
        if (_urlAjaxActions[locationUrl] === undefined) {
            var locationAction = false;
            if ($.options('#m-ajax').enabled) {
                for (var action in _ajaxifiedUrlOptions) {
                    var handled = false;
                    $.each(_ajaxifiedUrlOptions[action].exactUrls, function (urlIndex, url) {
                        if (!handled && locationUrl == url) {
                            var isException = false;
                            $.each(_ajaxifiedUrlOptions[action].urlExceptions, function (urlExceptionIndex, urlException) {
                                if (!isException && locationUrl.indexOf(urlException) != -1) {
                                    isException = true;
                                }
                            });
                            if (!isException) {
                                handled = true;
                                locationAction = action;
                            }
                        }
                    });
                    $.each(_ajaxifiedUrlOptions[action].partialUrls, function (urlIndex, url) {
                        if (!handled && locationUrl.indexOf(url) != -1) {
                            var isException = false;
                            $.each(_ajaxifiedUrlOptions[action].urlExceptions, function (urlExceptionIndex, urlException) {
                                if (!isException && locationUrl.indexOf(urlException) != -1) {
                                    isException = true;
                                }
                            });
                            if (!isException) {
                                handled = true;
                                locationAction = action;
                            }
                        }
                    });
                    if (handled) {
                        break;
                    }
                }
            }
            _urlAjaxActions[locationUrl] = locationAction;
        }
        return _urlAjaxActions[locationUrl];
    }

    function _processAjaxifiedUrl(url) {
        var action = _urlAjaxAction(url);
        if (action) {
            var selectors = $.options('#m-ajax').selectors[action];
            _ajaxifiedUrlOptions[action].callback(url, _lastAjaxActionSource, action, selectors);
            return false; // prevent default link click behavior
        }
        return true;
    }

    function setLocation(locationUrl, element) {
        var action = _urlAjaxAction(locationUrl);
        if (action) {
            _lastAjaxActionSource = element;
            locationUrl = window.decodeURIComponent(locationUrl);
            if (window.History && window.History.enabled) {
                window.History.pushState(null, window.title, locationUrl);
            }
            else {
                _processAjaxifiedUrl(locationUrl);
            }
        }
        else {
            oldSetLocation(locationUrl, element);
        }
        return false;
    }

    // the following function is executed when DOM ir ready. If not use this wrapper, code inside could fail if
    // executed when referenced DOM elements are still being loaded.
    $(function () {
        if (window.History && window.History.enabled) {
            $(window).bind('statechange', function () {
                var State = window.History.getState();
                _processAjaxifiedUrl(State.url);
            });
        }

        if (window.setLocation) {
            oldSetLocation = window.setLocation;
            window.setLocation = setLocation;
        }

        $(document).on('click', 'a', function() {
            var action = _urlAjaxAction(this.href);
            if (action) {
                return setLocation(this.href, this);
            }
        });
    });

    //endregion

    //region AJAX content get/update API

    $.mGetBlocksHtml = function(url, action, selectors, callback) {
        $(document).trigger('m-ajax-before', [selectors, url, action]);
        $.get(window.encodeURI(url + (url.indexOf('?') == -1 ? '?' : '&') + 'm-ajax=' + action + '&no_cache=1'))
            .done(function (response) {
                try {
                    response = $.parseJSON(response);
                    if (!response) {
                        if ($.options('#m-ajax').debug) {
                            alert('No response.');
                        }
                    }
                    else if (response.error) {
                        if ($.options('#m-ajax').debug) {
                            alert(response.error);
                        }
                    }
                    else {
                        callback(response, selectors, action, url);
                    }
                }
                catch (error) {
                    if ($.options('#m-ajax').debug) {
                        alert(response && typeof(response) == 'string' ? response : error);
                    }
                }
            })
            .fail(function (error) {
                if ($.options('#m-ajax').debug) {
                    alert(error.status + (error.responseText ? ': ' + error.responseText : ''));
                }
            })
            .complete(function () {
                $(document).trigger('m-ajax-after', [selectors, url, action]);
            });
    }
    $.mUpdateBlocksHtml = function(response) {
        $.dynamicReplace(response.update, $.options('#m-ajax').debug, true);
        if (response.options) {
            $.options(response.options);
        }
        if (response.script) {
            $.globalEval(response.script);
        }
        if (response.title) {
            document.title = response.title;
        }
    };

    $.mGetBlockHtml = function (url, action, callback) {
        $(document).trigger('m-ajax-before', [[], url, action]);
        $.get(window.encodeURI(url))
            .done(function (response) {
                try {
                    if (!response) {
                        if ($.options('#m-ajax').debug) {
                            alert('No response.');
                        }
                    }
                    else if (response.isJSON()) {
                        response = $.parseJSON(response);
                        if (response.error) {
                            if ($.options('#m-ajax').debug) {
                                alert(response.error);
                            }
                        }
                    }
                    else {
                        callback(response, url);
                    }
                }
                catch (error) {
                    if ($.options('#m-ajax').debug) {
                        alert(response && typeof(response) == 'string' ? response : error);
                    }
                }
            })
            .fail(function (error) {
                if ($.options('#m-ajax').debug) {
                    alert(error.status + (error.responseText ? ': ' + error.responseText : ''));
                }
            })
            .complete(function () {
                $(document).trigger('m-ajax-after', [[], url, action]);
            });
    }
    //endregion

    //region default progress visualization
    $(document).bind('m-ajax-before', function (e, selectors, url, action) {
        if ($.options('#m-ajax').overlay[action]) {
            if (selectors.length) {
                selectors.each(function (selector, selectorIndex) {
                    var left = 0, top = 0, right = 0, bottom = 0, assigned = false;
                    $(selector).each(function () {
                        var element = $(this);
                        var elOffset = element.offset(), elWidth = element.width(), elHeight = element.height();
                        if (!assigned || left > elOffset.left) {
                            left = elOffset.left;
                        }
                        if (!assigned || top > elOffset.top) {
                            top = elOffset.top;
                        }
                        if (!assigned || right < elOffset.left + elWidth) {
                            right = elOffset.left + elWidth;
                        }
                        if (!assigned || bottom < elOffset.top + elHeight) {
                            bottom = elOffset.top + elHeight;
                        }
                        assigned = true;
                    });
                    if (assigned) {
                        // create overlay
                        var overlay = $('<div class="m-overlay"> </div>');
                        overlay.appendTo(document.body);
                        overlay.css({left:left, top:top}).width(right - left).height(bottom - top);
                    }
                });
            }
            else {
                var overlay = $('<div class="m-overlay"> </div>');
                overlay.appendTo(document.body);
                overlay.css({left:0, top:0}).width($(document).width()).height($(document).height());
            }
        }

        if ($.options('#m-ajax').progress[action]) {
            $('#m-wait').show();
        }
    });
    $(document).bind('m-ajax-after', function (e, selectors, url, action) {
        // remove overlays
        if ($.options('#m-ajax').overlay[action]) {
            $('.m-overlay').remove();
        }
        if ($.options('#m-ajax').progress[action]) {
            $('#m-wait').hide();
        }
    });
    //endregion
})(jQuery);
