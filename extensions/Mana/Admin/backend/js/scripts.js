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
;(function(window, $, $get) {
	window.varienWindowOnload = function() {};
	
	// the following function is executed when DOM ir ready. If not use this wrapper, code inside could fail if
	// executed when referenced DOM elements are still being loaded.
	$(function() {
		$('input,select,textarea').live('change', function() {
			this.setHasChanges(this);
		});
		// UI logic for "use default value" checkboxes
		$('input.m-default').live('click', function() {
			var fieldId = this.id.substring(0, this.id.length - '_default'.length);
			if ($('#'+fieldId).length) {
				if (this.checked) {
					$('#'+fieldId).attr('disabled', true).addClass('disabled');
				}
				else {
					$('#'+fieldId).removeAttr('disabled').removeClass('disabled').focus();
				}
			}
			else {
				//throw 'Field for editing not found!';
			}
		});
		
		// UI logic for standard buttons
		$('button.m-close-action').live('click', function() {
			window.location.href = $.options('button.m-close-action').redirect_to;
		});
		$('button.m-save-action').live('click', function() {
			var request = [];
			if ($.options('edit-form') && $.options('edit-form').subforms) {
				$.each($.options('edit-form').subforms, function(index, formId) {
					$.merge(request, $(formId).serializeArray());
				});
			}
			$(document).trigger('m-before-save', [request]);

			$.mAdminPost($.options('button.m-save-action').action, request, function (response) {
                $.dynamicUpdate(response.update);
                if (response.refresh_redirect) {
                    window.location.href = response.refresh_redirect;
                }
            });
		});
		$('button.m-save-and-close-action').live('click', function() {
			var request = [];
			if ($.options('edit-form') && $.options('edit-form').subforms) {
				$.each($.options('edit-form').subforms, function(index, formId) {
					$.merge(request, $(formId).serializeArray());
				});
			}
            $(document).trigger('m-before-save', [request]);

            $.mAdminPost($.options('button.m-save-and-close-action').action, request, function (response) {
                $.dynamicUpdate(response.update);
                if (!response.error) {
                    window.location.href = $.options('button.m-save-and-close-action').redirect_to;
                }
            });
		});
	});
	$.mAdminPost = function(url, request, callback) {
        $('#loading-mask').show();
        $.post(url, request)
            .done(function(response) {
                try {
                    if (response.isJSON()) {
                        response = $.parseJSON(response);
                        if (response.error && response.message) {
                            alert(response.message);
                        }
                        if (response.ajaxExpired && response.ajaxRedirect) {
                            setLocation(response.ajaxRedirect);
                        }
                        callback(response);
                    }
                    else {
                        callback(response);
                    }
                }
                catch (error) {
                    $.errorUpdate($.options('edit-form').messagesSelector, response || error.message || error);
                }
            })
            .fail(function (error) {
                $.errorUpdate($.options('edit-form').messagesSelector, error);
            })
            .complete(function () {
                $('#loading-mask').hide();
            });
    };
	$.mAdminResponse = function(response, callback) {
        try {
            if (response.isJSON()) {
                if (response.error) {
                    alert(response.message);
                }
                if (response.ajaxExpired && response.ajaxRedirect) {
                    setLocation(response.ajaxRedirect);
                }
            }
            else {
                callback(response);
            }
        }
        catch (error) {
            $.errorUpdate($.options('edit-form').messagesSelector, response || error.message || error);
        }
    };

    //region Product Chooser API
    (function () {
        var _chooserContent = null;
        var _chooserDialog = null;
        var _options;
        $.mChooseProducts = function(options) {
            _options = $.extend({
                // url typically comes with $.options
                // title typically comes with $.options but can be overridden by options parameter
                params: function () { return {}; }, // params: callback is typically provided by options parameter
                result: function () {} // result: callback is typically provided by options parameter
            }, $.options('#m_product_chooser_dialog'), options);

            if (!_chooserContent) {
                $.get(_options.url, _options.params())
                    .done(function (response) {
                        _chooserContent = '<div id="m_product_chooser_dialog">' + (response || '') + '</div>';
                        _openChooser();
                    });
            }
            else {
                _openChooser();
            }
        };
        function _closeChooser(dialog) {
            if (!dialog) {
                dialog = _chooserDialog;
            }
            if (dialog) {
                dialog.close();
            }
            _chooserDialog = null;
            _chooserContent = null;
        }
        function _openChooser() {
            _chooserDialog = Dialog.info(_chooserContent, {
                draggable:true, resizable:true, closable:true, className:"magento", windowClassName:"popup-window",
                title:_options.title,
                top:0, width:950, height:680, zIndex:1000,
                recenterAuto:false, hideEffect:Element.hide, destroyOnClose:true,
                showEffect:Element.show, id:"widget-chooser",
                onClose:_closeChooser
            });
            _chooserContent.evalScripts.bind(_chooserContent).call();
            var selected = {};
            $get('widget-chooser').observe('product:cancelled', function () {
                _closeChooser();
                _options.result(false);
            });
            $get('widget-chooser').observe('product:confirmed', function () {
                _closeChooser();
                var ids = [];
                for (var id in selected) {
                    ids.push(id);
                }
                _options.result(ids.length ? ids : false);
            });
            function _select() {
                var id = $(this).val();
                if ($(this).is(':checked')) {
                    selected[id] = id;
                }
                else {
                    if (selected[id]) {
                        delete selected[id];
                    }
                }
            }

            $('#m_product_chooser_table .checkbox.entities').live('change', function () {
                _select.apply(this);
            });
            var _oldSelectAll = m_product_chooserJsObject.checkCheckboxes;
            m_product_chooserJsObject.checkCheckboxes = function (el) {
                _oldSelectAll.call(m_product_chooserJsObject, el);
                $('#m_product_chooser_table .checkbox.entities').each(function (checkboxIndex, checkbox) {
                    _select.apply(checkbox);
                });
            };
        }

    })();
    //endregion

    //region CMS Block Chooser API
    (function () {
        var _chooserContent = null;
        var _chooserDialog = null;
        var _options;
        $.mChooseCmsBlocks = function (options) {
            _options = $.extend({
                // url typically comes with $.options
                // title typically comes with $.options but can be overridden by options parameter
                params:function () {
                    return {};
                }, // params: callback is typically provided by options parameter
                result:function () {
                } // result: callback is typically provided by options parameter
            }, $.options('#m_cmsblock_chooser_dialog'), options);

            if (!_chooserContent) {
                $.get(_options.url, _options.params())
                    .done(function (response) {
                        _chooserContent = '<div id="m_cmsblock_chooser_dialog">' + (response || '') + '</div>';
                        _openChooser();
                    });
            }
            else {
                _openChooser();
            }
        };
        function _closeChooser(dialog) {
            if (!dialog) {
                dialog = _chooserDialog;
            }
            if (dialog) {
                dialog.close();
            }
            _chooserDialog = null;
            _chooserContent = null;
        }

        function _openChooser() {
            _chooserDialog = Dialog.info(_chooserContent, {
                draggable:true, resizable:true, closable:true, className:"magento", windowClassName:"popup-window",
                title:_options.title,
                top:0, width:950, height:680, zIndex:1000,
                recenterAuto:false, hideEffect:Element.hide, destroyOnClose:true,
                showEffect:Element.show, id:"widget-chooser",
                onClose:_closeChooser
            });
            _chooserContent.evalScripts.bind(_chooserContent).call();
            var selected = {};
            $get('widget-chooser').observe('cmsblock:cancelled', function () {
                _closeChooser();
                _options.result(false);
            });
            $get('widget-chooser').observe('cmsblock:confirmed', function () {
                _closeChooser();
                var ids = [];
                for (var id in selected) {
                    ids.push(id);
                }
                _options.result(ids.length ? ids : false);
            });
            function _select() {
                var id = $(this).val();
                if ($(this).is(':checked')) {
                    selected[id] = id;
                }
                else {
                    if (selected[id]) {
                        delete selected[id];
                    }
                }
            }

            $('#m_cmsblock_chooser_table .checkbox.entities').live('change', function () {
                _select.apply(this);
            });
//            var _oldSelectAll = m_product_chooserJsObject.checkCheckboxes;
//            m_product_chooserJsObject.checkCheckboxes = function (el) {
//                _oldSelectAll.call(m_product_chooserJsObject, el);
//                $('#m_product_chooser_table .checkbox.entities').each(function (checkboxIndex, checkbox) {
//                    _select.apply(checkbox);
//                });
//            };
        }

    })();
    //endregion

})(window, jQuery, $);
