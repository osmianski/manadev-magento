Mana.define('Mana/Sorting/Method/TabContainer', ['jquery', 'Mana/Admin/Container', 'singleton:Mana/Core'],
function ($, Container, core) {
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