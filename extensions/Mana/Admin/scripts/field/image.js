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
    function _buttons(tr) {
        if ($(tr).find('.m-default').attr('checked') == 'checked') {
            $(tr).find('.mfi-buttons .m-button').hide();
        }
        else {
            $(tr).find('.mfi-buttons .m-button').show();
            if ($(tr).find('div.field-image').prev().val() == '') {
                $(tr).find('.mfi-buttons .change.m-button, .mfi-buttons .delete.m-button').hide();
            }
            else {
                $(tr).find('.mfi-buttons .add.m-button').hide();
            }
        }
    }
    $(function() {
        $(document).bind('m-image-field-reset', function(e, tr) {
            // file uploader initialization
            // the following shows the button in specified element with file upload behavior on click
            var addUploader = new qq.FileUploader({
                // pass the dom node (ex. $(selector)[0] for jQuery users)
                element: $(tr).find('.add.m-button')[0],
                // path to server-side upload script
                action: $.options("#m-image-helper").uploadUrl,
                params: { type: 'image', form_key: FORM_KEY },
                onSubmit: function(id, fileName) {
                    addUploader._options.params.id = tr.id;
                },
                // when upload complete we should update image in grid
                onComplete: function(id, fileName, responseJSON){
                    if (responseJSON.id) {
                        var baseUrl = $.options('#m-image-helper').baseUrl + '/';
                        $('#'+responseJSON.id).find('div.field-image').prev().val(responseJSON.relativeUrl);
                        $('#'+responseJSON.id).find('div.field-image').css({'background-image': 'url(' + responseJSON.url + ')'});
                        _buttons(tr);
                        $(tr).find('.add.m-button').val('');
                    }
                }
            });
            var changeUploader = new qq.FileUploader({
                // pass the dom node (ex. $(selector)[0] for jQuery users)
                element: $(tr).find('.change.m-button')[0],
                // path to server-side upload script
                action: $.options("#m-image-helper").uploadUrl,
                params: { type: 'image', form_key: FORM_KEY },
                onSubmit: function(id, fileName) {
                    changeUploader._options.params.id = tr.id;
                },
                // when upload complete we should update image in grid
                onComplete: function(id, fileName, responseJSON){
                    if (responseJSON.id) {
                        var baseUrl = $.options('#m-image-helper').baseUrl + '/';
                        $('#'+responseJSON.id).find('div.field-image').prev().val(responseJSON.relativeUrl);
                        $('#'+responseJSON.id).find('div.field-image').css({'background-image': 'url(' + responseJSON.url + ')'});
                        _buttons(tr);
                        $(tr).find('.change.m-button').val('');
                    }
                }
            });
            $(tr).find('.delete.m-button').click(function() {
                $(tr).find('div.field-image').prev().val('');
                $(tr).find('div.field-image').css({'background-image': ''});
                 _buttons(tr);
            });
            $(tr).find('.m-default').click(function() {
                 _buttons(tr);
            });
            _buttons(tr);
        });
    });
})(jQuery);
