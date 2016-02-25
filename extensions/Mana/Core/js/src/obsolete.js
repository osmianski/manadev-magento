//region (Obsolete) additional jQuery functions used in MANAdev extensions
(function($) {
	// this variables are private to this code block
	var _translations = {};
	var _options = {};

	// Default usage of this function is to pass a string in original language and get translated string as a 
	// result. This same function is also used to register original and translated string pairs - in this case
	// plain object with mappings is passed as the only parameter. Anyway, we expect the only parameter to be 
	// passed
	$.__ = function(key) {
		if (typeof key === "string") { // do translation
			var args = arguments;
			args[0] = _translations[key] ? _translations[key] : key;
			return $.vsprintf(args);
		}
		else { // register translation pairs
			_translations = $.extend(_translations, key);
		}
	};
	// Default usage of this function is to pass a CSS selector and get plain object of associated options as 
	// a result. This same function is used to register selector-object pairs in this case plain object with 
	// with mappings is passed as the only parameter. Anyway, we expect the only parameter to be passed
	$.options = function (selector) {
		if (typeof selector === "string") { // return associated options
			return _options[selector];
		}
		else { // register selector-options pairs
			_options = $.extend(true, _options, selector);
		}
		$(document).trigger('m-options-changed');
	};
	
	$.dynamicUpdate = function (update) {
		if (update) {
			$.each(update, function(index, update) {
				$(update.selector).html(update.html);
			});
		}
	}
	$.dynamicReplace = function (update, loud, decode) {
		if (update) {
			$.each(update, function(selector, html) {
				var selected = $(selector);
				if (selected.length) {
					var first = $(selected[0]);
					if (selected.length > 1) {
						selected.slice(1).remove();
					}
					first.replaceWith(decode ? $.utf8_decode(html) : html);
				}
				else {
					if (loud) {
						throw 'There is no content to replace.';
					}
				}
				//console.log('Selector: ' + selector);
				//console.log('HTML: ' + html);
			});
		}
	}
	
	$.errorUpdate = function(selector, error) {
		if (!selector) {
			selector = '#messages';
		}
		var messages = $(selector);
		if (messages.length) {
			messages.html('<ul class="messages"><li class="error-msg"><ul><li>' + error + '</li></ul></li></ul>');
		}
		else {
			alert(error);
		}
	}
	
	// Array Remove - By John Resig (MIT Licensed)
	$.arrayRemove = function(array, from, to) {
	  var rest = array.slice((to || from) + 1 || array.length);
	  array.length = from < 0 ? array.length + from : from;
	  return array.push.apply(array, rest);
	};
	$.mViewport = function() {
		var m = document.compatMode == 'CSS1Compat';
		return {
			l : window.pageXOffset || (m ? document.documentElement.scrollLeft : document.body.scrollLeft),
			t : window.pageYOffset || (m ? document.documentElement.scrollTop : document.body.scrollTop),
			w : window.innerWidth || (m ? document.documentElement.clientWidth : document.body.clientWidth),
			h : window.innerHeight || (m ? document.documentElement.clientHeight : document.body.clientHeight)
		};
	}
	$.mStickTo = function(el, what) {
		var pos = $(el).offset();
		var viewport = $.mViewport();
		var top = pos.top + el.offsetHeight;
		var left = pos.left + (el.offsetWidth - what.outerWidth()) / 2;
		if (top + what.outerHeight() > viewport.t + viewport.h) {
			top = pos.top - what.outerHeight();
		}
		if (left + what.outerWidth() > viewport.l + viewport.w) {
			left = pos.left + el.offsetWidth - what.outerWidth();
		}
		what.css({left: left + 'px', top: top + 'px'});
	}

	$.fn.mMarkAttr = function (attr, condition) {
	    this.prop(attr, condition);
		//if (condition) {
		//	this.attr(attr, attr);
		//}
		//else {
		//	this.removeAttr(attr);
		//}
		return this;
	}; 
	// the following function is executed when DOM ir ready. If not use this wrapper, code inside could fail if
	// executed when referenced DOM elements are still being loaded.
	$(function() {
		// fix for IE 7 and IE 8 where dom:loaded may fire too early
		try {
		    if (window.mainNav) {
                window.mainNav("nav", {"show_delay":"100", "hide_delay":"100"});
            }
		}
		catch (e) {
			
		}
	});

    $.base64_decode = function (data) {
        // Decodes string using MIME base64 algorithm
        //
        // version: 1109.2015
        // discuss at: http://phpjs.org/functions/base64_decode
        // +   original by: Tyler Akins (http://rumkin.com)
        // +   improved by: Thunder.m
        // +      input by: Aman Gupta
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   bugfixed by: Onno Marsman
        // +   bugfixed by: Pellentesque Malesuada
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +      input by: Brett Zamir (http://brett-zamir.me)
        // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // -    depends on: utf8_decode
        // *     example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
        // *     returns 1: 'Kevin van Zonneveld'
        // mozilla has this native
        // - but breaks in 2.0.0.12!
        //if (typeof this.window['btoa'] == 'function') {
        //    return btoa(data);
        //}
        var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
            ac = 0,
            dec = "",
            tmp_arr = [];

        if (!data) {
            return data;
        }

        data += '';

        do { // unpack four hexets into three octets using index points in b64
            h1 = b64.indexOf(data.charAt(i++));
            h2 = b64.indexOf(data.charAt(i++));
            h3 = b64.indexOf(data.charAt(i++));
            h4 = b64.indexOf(data.charAt(i++));

            bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

            o1 = bits >> 16 & 0xff;
            o2 = bits >> 8 & 0xff;
            o3 = bits & 0xff;

            if (h3 == 64) {
                tmp_arr[ac++] = String.fromCharCode(o1);
            } else if (h4 == 64) {
                tmp_arr[ac++] = String.fromCharCode(o1, o2);
            } else {
                tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
            }
        } while (i < data.length);

        dec = tmp_arr.join('');
        dec = $.utf8_decode(dec);

        return dec;
    };
    $.utf8_decode = function (str_data) {
        // Converts a UTF-8 encoded string to ISO-8859-1
        //
        // version: 1109.2015
        // discuss at: http://phpjs.org/functions/utf8_decode
        // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
        // +      input by: Aman Gupta
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   improved by: Norman "zEh" Fuchs
        // +   bugfixed by: hitwork
        // +   bugfixed by: Onno Marsman
        // +      input by: Brett Zamir (http://brett-zamir.me)
        // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // *     example 1: utf8_decode('Kevin van Zonneveld');
        // *     returns 1: 'Kevin van Zonneveld'
        var tmp_arr = [],
            i = 0,
            ac = 0,
            c1 = 0,
            c2 = 0,
            c3 = 0;

        str_data += '';

        while (i < str_data.length) {
            c1 = str_data.charCodeAt(i);
            if (c1 < 128) {
                tmp_arr[ac++] = String.fromCharCode(c1);
                i++;
            } else if (c1 > 191 && c1 < 224) {
                c2 = str_data.charCodeAt(i + 1);
                tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
                i += 2;
            } else {
                c2 = str_data.charCodeAt(i + 1);
                c3 = str_data.charCodeAt(i + 2);
                tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
        }

        return tmp_arr.join('');
    };

    var _popupFadeoutOptions = { overlayTime: 500, popupTime: 1000, callback: null };
    $.mSetPopupFadeoutOptions = function(options) {
        _popupFadeoutOptions = options;
    }
    $.fn.extend({
        mPopup: function(name, options) {
            var o = $.extend({
                fadeOut: { overlayTime: 0, popupTime:500, callback:null },
                fadeIn: { overlayTime: 0, popupTime:500, callback: null },
                overlay: { opacity: 0.2},
                popup: { contentSelector:'.' + name + '-text', containerClass:'m-' + name + '-popup-container', top:100 }

            }, options);
            $(document).on('click', this, function () {
                if ($.mPopupClosing()) {
                    return false;
                }
                // preparations
                var html = $(o.popup.contentSelector).html();
                $.mSetPopupFadeoutOptions(o.fadeOut);

                // put overlay to prevent interaction with the page and to catch 'cancel' mouse clicks
                var overlay = $('<div class="m-popup-overlay"> </div>');
                overlay.appendTo(document.body);
                overlay.css({left:0, top:0}).width($(document).width()).height($(document).height());
                overlay.animate({ opacity:o.overlay.opacity }, o.fadeIn.overlayTime, function () {
                    // all this code is called when overlay animation is over

                    // fill popup with content
                    $('#m-popup')
                        .css({"width":"auto", "height":"auto"})
                        .html(html)
                        .addClass(o.popup.containerClass)
                        .css("top", (($(window).height() - $('#m-popup').outerHeight()) / 2) - o.popup.top + $(window).scrollTop() + "px")
                        .css("left", (($(window).width() - $('#m-popup').outerWidth()) / 2) + $(window).scrollLeft() + "px")

                    // get intended height and set initial height to 0
                    var popupHeight = $('#m-popup').height();
                    $('#m-popup').show().height(0);
                    $('#m-popup').hide().css({"height":"auto"});

                    // calculate intended popup position
                    var css = {
                        left:$('#m-popup').css('left'),
                        top:$('#m-popup').css('top'),
                        width:$('#m-popup').width() + "px",
                        height:$('#m-popup').height() + "px"
                    };

                    // adjust (the only) child of popup container element
                    $('#m-popup').children().each(function () {
                        $(this).css({
                            width:($('#m-popup').width() + $(this).width() - $(this).outerWidth()) + "px",
                            height:($('#m-popup').height() + $(this).height() - $(this).outerHeight()) + "px"
                        });
                    });

                    // make popup a point
                    $('#m-popup')
                        .css({
                            top:($(window).height() / 2) - o.popup.top + $(window).scrollTop() + "px",
                            left:($(window).width() / 2) + $(window).scrollLeft() + "px",
                            width:0 + "px",
                            height:0 + "px"
                        })
                        .show();

                    // explode popup to intended size
                    $('#m-popup').animate(css, o.fadeIn.popupTime, function () {
                        if (o.fadeIn.callback) {
                            o.fadeIn.callback();
                        }
                    });
                });

                // prevent following to target link of <a> tag
                return false;
            });
        }
    });
    var _popupClosing = false;
    $.mPopupClosing = function (value) {
        if (value !== undefined) {
            _popupClosing = value;
        }
        return _popupClosing;
    };
    $.mClosePopup = function () {
        $.mPopupClosing(true);
        $('.m-popup-overlay').fadeOut(_popupFadeoutOptions.overlayTime, function() {
            $('.m-popup-overlay').remove();
            $('#m-popup').fadeOut(_popupFadeoutOptions.popupTime, function() {
                if (_popupFadeoutOptions.callback) {
                    _popupFadeoutOptions.callback();
                }
                $.mPopupClosing(false);
            });
        })
        return false;
    };

})(jQuery);
//endregion