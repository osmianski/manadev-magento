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
	var _td = null;

	function _onHelperShow(td, helper) {
	    if ($.gridData(td, 'show_use_default')) {
	        $(helper).find('.use-default').show();
            $(helper).find('input.m-default')
                .mMarkAttr('checked', $.gridData(td, 'is_default'))
                .mMarkAttr('disabled', $.gridData(td, 'is_default_disabled'))
                .css({'margin-bottom': '10px'});
        }
        else {
            $(helper).find('.use-default').hide();
        }
        var value = $.gridData(td, 'value');
        if ($.gridData(td, 'show_use_default') && $(helper).find('input.m-default').attr('checked') == 'checked') {
            $('#m-image-helper .mi-buttons').hide();
        }
        else {
            $('#m-image-helper .mi-buttons').show();
        }
        if (value) {
            $('#m-image-helper .mi-buttons .add').hide();
            $('#m-image-helper .mi-buttons .change').show();
            $('#m-image-helper .mi-buttons .delete').show();
        }
        else {
            $('#m-image-helper .mi-buttons .add').show();
            $('#m-image-helper .mi-buttons .change').hide();
            $('#m-image-helper .mi-buttons .delete').hide();
        }

		_helper = helper;
		_td = td;
	}
	function _onHelperHide(td, helper) {
		$.gridData(td, {
		    is_default: $(helper).find('input.m-default').attr('checked') == 'checked'
		});
		_helper = null;
		//_td = null;
	}
	// the following function is executed when DOM ir ready. If not use this wrapper, code inside could fail if
	// executed when referenced DOM elements are still being loaded.
	$(function() {
		$('.ct-image').live('mouseover', function() {
			if ($.gridData(this, 'show_helper')) { 
				$.helperPopup({
					host: this, 
					helper: '#m-image-helper',
					onShow: _onHelperShow,
					onHide: _onHelperHide
				});
			}
		});

		// file uploader initialization
		// the following shows the button in specified element with file upload behavior on click
		var addUploader = new qq.FileUploader({
		    // pass the dom node (ex. $(selector)[0] for jQuery users)
		    element: $('#m-image-helper .add.m-button')[0],
		    // path to server-side upload script
		    action: $.options("#m-image-helper").uploadUrl,
		    params: { type: 'image', form_key: FORM_KEY },
		    onSubmit: function(id, fileName) {
		        addUploader._options.params.id = $(_td).find('div')[0].id;
		    },
		    // when upload complete we should update image in grid
		    onComplete: function(id, fileName, responseJSON){
		        if (responseJSON.id) {
                    var baseUrl = $.options('#m-image-helper').baseUrl + '/';
                    $.gridData($('#'+responseJSON.id).parent()[0], {
                        value: responseJSON.relativeUrl
                    });
                    $('#'+responseJSON.id).css({'background-image': 'url(' + responseJSON.url + ')'});
                    $('#m-image-helper .add.m-button').val('');
                }
                $.hideHelperPopup();
		    }
		});
        var changeUploader = new qq.FileUploader({
            // pass the dom node (ex. $(selector)[0] for jQuery users)
            element: $('#m-image-helper .change.m-button')[0],
            // path to server-side upload script
            action: $.options("#m-image-helper").uploadUrl,
            params: { type: 'image', form_key: FORM_KEY },
            onSubmit: function(id, fileName) {
                changeUploader._options.params.id = $(_td).find('div')[0].id;
            },
            // when upload complete we should update image in grid
            onComplete: function(id, fileName, responseJSON){
                if (responseJSON.id) {
                    var baseUrl = $.options('#m-image-helper').baseUrl + '/';
                    $.gridData($('#'+responseJSON.id).parent()[0], {
                        value: responseJSON.relativeUrl
                    });
                    $('#'+responseJSON.id).css({'background-image': 'url(' + responseJSON.url + ')'});
                    $('#m-image-helper .change.m-button').val('');
                }
                $.hideHelperPopup();
            }
        });
        $('#m-image-helper .delete.m-button').click(function() {
            $.gridData(_td, { value: ''});
            $(_td).find('div').css({'background-image': ''});
            $.hideHelperPopup();
        });
        $('#m-image-helper input.m-default').click(function() {
            if ($(this).attr('checked') == 'checked') {
                $('#m-image-helper .mi-buttons').hide();
            }
            else {
                $('#m-image-helper .mi-buttons').show();
            }
        });
	});
})(jQuery);
