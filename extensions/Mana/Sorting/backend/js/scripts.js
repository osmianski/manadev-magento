/**
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // for better JS merging



Mana.define('Mana/Sorting/Method/ListContainer', ['jquery', 'Mana/Admin/Container'],
function ($, Container)
{
    return Container.extend('Mana/Sorting/Method/ListContainer', {
        _subscribeToBlockEvents: function () {

            return this
                ._super()
                .on('load', this, function () {
                    if (this.getChild('create-method')) this.getChild('create-method').on('click', this,
                        this.createMethod);
                })
                .on('unload', this, function () {
                    if (this.getChild('create-method')) this.getChild('create-method').off('click', this,
                        this.createMethod);
                });
        },
        createMethod: function () {
            setLocation(this.getUrl('create-method'));
        }

    });
});


Mana.define('Mana/Sorting/Method/TabContainer', ['jquery', 'Mana/Admin/Container', 'singleton:Mana/Admin/Expression'],
function ($, Container, expression) {
    return Container.extend('Mana/Sorting/Method/TabContainer', {
        _subscribeToHtmlEvents: function() {
            var self = this;

            return this._super()
                .on('bind', this, function() {
                })
                .on('unbind', this, function() {
                })
        },
        _subscribeToBlockEvents: function () {
            var self = this;
            return this
                ._super()
                .on('load', this, function () {
                    this.updateAttributes();
                    if (this.getChild('delete')) this.getChild('delete').on('click', this, this.deleteRecord);
                    $.each(this.getFields(), function (fieldName) {
                        var field = self.getField(fieldName);
                        if (field) field.on('change', self, self.fieldChanged);
                    });
                })
                .on('unload', this, function () {
                    if (this.getChild('delete')) this.getChild('delete').off('click', this, this.deleteRecord);
                    $.each(this.getFields(), function (fieldName) {
                        var field = self.getField(fieldName);
                        if (field) field.on('change', self, self.fieldChanged);
                    });
                });
        },
        deleteRecord: function() {
            deleteConfirm(this.getText('delete-confirm'), this.getUrl('delete'));
        },
        fieldChanged: function(e) {
            function underscoreToCamelCase(string) {
                var words = string.split('_');
                for(var i=0; i < words.length; i++) {
                    words[i] = words[i].charAt(0).toUpperCase() + words[i].slice(1).toLowerCase()
                }
                return words.join('');
            }

            var strField = e.target.getName();
            var fieldSuffix = underscoreToCamelCase(strField);
            var fieldChangeFunction = "update" + fieldSuffix;
            var fieldUseDefaultFunction = "useDefault" + fieldSuffix;

            if(typeof this[fieldChangeFunction] === "function") {
                this[fieldChangeFunction]();
            }
            if (typeof this[fieldUseDefaultFunction] === "function") {
                this[fieldUseDefaultFunction](strField);
            } else {
                this.defaultUseDefaultProcess(strField);
            }
        },
        defaultUseDefaultProcess: function(fieldName) {
            var field = this.getField(fieldName);
            if (field.useDefault()) {
                field.setValue(this.getJsonData('global', fieldName));
            }
        },
        updateTitle: function() {
            this.useDefaultUrlKey();
        },
        useDefaultTitle: function() {
            this.defaultUseDefaultProcess('title');
            this.useDefaultUrlKey();
        },
        useDefaultUrlKey: function() {
            var field = this.getField('url_key');
            var title = this.getField('title').getValue();
            var url_key = expression.seoify(title);
            if(typeof field !== "undefined" && field.useDefault()) {
                field.setValue(url_key);
            }
        },
        getAttrCount: function () {
            return 5;
        },
        updateAttributeId0: function() {
            this.updateAttributes();
        },
        updateAttributeId1: function () {
            this.updateAttributes();
        },
        updateAttributeId2: function () {
            this.updateAttributes();
        },
        updateAttributeId3: function () {
            this.updateAttributes();
        },
        updateAttributeId4: function () {
            this.updateAttributes();
        },
        updateAttributes: function() {
            var lastIndex = -1;
            var values = {};
            var i, field, value;
            var self = this;
            for (i = 0; i < this.getAttrCount(); i++) {
                field = this.getField('attribute_id_' + i);
                if (value = field.getValue()) {
                    lastIndex = i;
                    values[value] = field;
                }
            }
            for (i = 0; i < this.getAttrCount(); i++) {
                field = this.getField('attribute_id_' + i);
                if (i > lastIndex + 1) {
                    field.$().hide();
                }
                else {
                    field.$().show();
                }
                field.$field().find('option').each(function() {
                    if (value = $(this).val()) {
                        if (values[value] !== undefined && values[value] != field) {
                            $(this).hide();
                        }
                        else {
                            $(this).show();
                        }
                    }
                    else {
                        if (i < lastIndex) {
                            $(this).hide();
                        }
                        else {
                            $(this).show();
                        }
                    }
                });
            }
        }
    });
});

Mana.define('Mana/Sorting/Method/TabContainer/Global',
['jquery', 'Mana/Sorting/Method/TabContainer'],
function ($, TabContainer) {
    return TabContainer.extend('Mana/Sorting/Method/TabContainer/Global', {
    });
});

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

//# sourceMappingURL=sorting.js.map