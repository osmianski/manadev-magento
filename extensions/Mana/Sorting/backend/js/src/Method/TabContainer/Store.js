Mana.define('Mana/Sorting/Method/TabContainer/Store',
['jquery', 'Mana/Sorting/Method/TabContainer', 'singleton:Mana/Admin/Expression'],
function ($, TabContainer, expression) {
    return TabContainer.extend('Mana/Sorting/Method/TabContainer/Store', {
        useDefaultUrlKey: function() {
            var field = this.getField('url_key');
            if (field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'url_key')) {
                    field.setValue(this.getJsonData('global', 'url_key'));
                } else {
                    var title = this.getField('title').getValue();
                    var url_key = expression.seoify(title);
                    field.setValue(url_key);
                }
            }
        }

    });
});