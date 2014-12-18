Mana.define('Mana/Content/Wysiwyg', ['jquery', 'Mana/Admin/Field/TextArea'],
function ($, TextArea)
{
    return TextArea.extend('Mana/Content/Wysiwyg', {
        _subscribeToHtmlEvents: function () {
            var self = this;
            function initTinyMce() {
                if (self.useDefault()) {
                    self.disable();
                }
                else {
                    self.enable();
                }
                function changeValue(o) {
                    self.setValue(o.getContent());
                }
                self.$editor().onKeyUp.remove(changeValue);
                self.$editor().onKeyUp.add(changeValue);
            }
            return this
                ._super()
                .on('bind', this, function () {
                    varienGlobalEvents.attachEventHandler("tinymceBeforeSetContent", initTinyMce);
                })
                .on('unbind', this, function () {
                    varienGlobalEvents.removeEventHandler("tinymceBeforeSetContent", initTinyMce);
                });
        },

        $editor: function() {
            if(typeof window.tinymce !== "undefined") {
                return window.tinymce.activeEditor;
            }
            return false;
        },
        disable: function () {
            this._super();
            if(this.$editor()) {
                this.$editor().getBody().setAttribute('contenteditable', false);
            }
        },
        enable: function () {
            this._super();
            if (this.$editor()) {
                this.$editor().getBody().setAttribute('contenteditable', true);
            }
        }

    });
});
