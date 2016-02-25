/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

;var manaWysiwygEditor = {
    overlayShowEffectOptions:null,
    overlayHideEffectOptions:null,
    open:function (editorUrl, elementId, storeId) {
        if (editorUrl && elementId) {
            new Ajax.Request(editorUrl, {
                parameters:{
                    element_id:elementId + '_editor',
                    store_id:storeId
                },
                onSuccess:function (transport) {
                    try {
                        this.openDialogWindow(transport.responseText, elementId);
                    } catch (e) {
                        alert(e.message);
                    }
                }.bind(this)
            });
        }
    },
    openDialogWindow:function (content, elementId) {
        this.overlayShowEffectOptions = Windows.overlayShowEffectOptions;
        this.overlayHideEffectOptions = Windows.overlayHideEffectOptions;
        Windows.overlayShowEffectOptions = {duration:0};
        Windows.overlayHideEffectOptions = {duration:0};

        Dialog.confirm(content, {
            draggable:true,
            resizable:true,
            closable:true,
            className:"magento",
            windowClassName:"popup-window",
            title:'WYSIWYG Editor',
            width:950,
            height:555,
            zIndex:1000,
            recenterAuto:false,
            hideEffect:Element.hide,
            showEffect:Element.show,
            id:"mana-wysiwyg-editor",
            buttonClass:"form-button",
            okLabel:"Submit",
            ok:this.okDialogWindow.bind(this),
            cancel:this.closeDialogWindow.bind(this),
            onClose:this.closeDialogWindow.bind(this),
            firedElementId:elementId
        });

        content.evalScripts.bind(content).defer();

        $(elementId + '_editor').value = $(elementId).value;
    },
    okDialogWindow:function (dialogWindow) {
        if (dialogWindow.options.firedElementId) {
            var el = $(dialogWindow.options.firedElementId);
            wysiwygObj = eval('wysiwyg' + dialogWindow.options.firedElementId + '_editor');
            wysiwygObj.turnOff();
            if (tinyMCE.get(wysiwygObj.id)) {
                el.setHasChanges(el);
                el.value = tinyMCE.get(wysiwygObj.id).getContent();

            } else {
                if ($(dialogWindow.options.firedElementId + '_editor')) {
                    el.setHasChanges(el);
                    el.value = $(dialogWindow.options.firedElementId + '_editor').value;
                }
            }
        }
        this.closeDialogWindow(dialogWindow);
    },
    closeDialogWindow:function (dialogWindow) {
        // remove form validation event after closing editor to prevent errors during save main form
        if (typeof varienGlobalEvents != undefined && editorFormValidationHandler) {
            varienGlobalEvents.removeEventHandler('formSubmit', editorFormValidationHandler);
        }

        //IE fix - blocked form fields after closing
        $(dialogWindow.options.firedElementId).focus();

        //destroy the instance of editor
        wysiwygObj = eval('wysiwyg' + dialogWindow.options.firedElementId + '_editor');
        if (tinyMCE.get(wysiwygObj.id)) {
            tinyMCE.execCommand('mceRemoveControl', true, wysiwygObj.id);
        }

        dialogWindow.close();
        Windows.overlayShowEffectOptions = this.overlayShowEffectOptions;
        Windows.overlayHideEffectOptions = this.overlayHideEffectOptions;
    }
};

// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation:
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
(function ($) {
    $(function () {
        $(document).on('click', '.m-wysiwyg', function () {
            var elementId = $(this).prev()[0].id;
            manaWysiwygEditor.open($.options('#mana-wysiwyg-editor').url, elementId, $.options('#mana-wysiwyg-editor').storeId);
        });
    });
})(jQuery);

