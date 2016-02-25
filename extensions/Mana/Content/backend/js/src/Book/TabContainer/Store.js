Mana.define('Mana/Content/Book/TabContainer/Store',
['jquery', 'Mana/Content/Book/TabContainer', 'singleton:Mana/Admin/Expression'],
function ($, TabContainer, expression) {
    return TabContainer.extend('Mana/Content/Book/TabContainer/Store', {
        onChangeTitle: function() {
            var field = this.getField('title');
            if(field.useDefault()) {
                field.setValue(this.getJsonData('global', 'title'));
            }
            this._super();
        },
        onChangeUrlKey: function() {
            var field = this.getField('url_key');
            if((typeof field !== "undefined" && field.useDefault()) || (field.constructor.name = "Mana_Admin_Field_Hidden")) {
                if (this.getJsonData('global-is-custom', 'url_key')) {
                    field.setValue(this.getJsonData('global', 'url_key'));
                } else {
                    var title = this.getField('title').getValue();
                    var url_key = expression.seoify(title);
                    field.setValue(url_key);
                }
                this.getField('url_key_preview').$().find(".value span")[0].innerHTML = field.getValue();
            }
            if(typeof field === "undefined") {
                this.initChangesObj()['url_key'] = {
                    value: url_key,
                    isDefault: 1
                };
                this.setToBlackIfNoChanges();
            }
        },
        onChangeMetaTitle: function() {
            var field = this.getField('meta_title');
            if(typeof field !== "undefined" && field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'meta_title')) {
                    field.setValue(this.getJsonData('global', 'meta_title'));
                } else {
                    var title = this.getField('title').getValue();
                    field.setValue(title);
                }
            }
        },
        onChangeTags: function() {
            var field = this.getField('meta_keywords');
            if (typeof field !== "undefined" && field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'meta_keywords')) {
                    field.setValue(this.getJsonData('global', 'meta_keywords'));
                } else {
                    field.setValue(this.getField('tags').getValue());
                }
            }
        }
    });
});