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
            var i, field, value, hiddenField, tmpVal, optgroup, options;
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
                hiddenField = this.getField('attribute_id_' + i + '_hidden');
                if (i > lastIndex + 1) {
                    field.$().hide();
                    this.getField('attribute_id_' + i + '_sortdir').$().hide();
                }
                else {
                    field.$().show();
                    this.getField('attribute_id_' + i + '_sortdir').$().show();
                }
                if(field.getValue() == "") {
                    this.getField('attribute_id_' + i + '_sortdir').$().hide();
                }
                field.$field().find('option').each(function() {
                    if (value = $(this).val()) {
                        if (values[value] !== undefined && values[value] != field) {
                            optgroup = hiddenField.$field().find("optgroup[label='"+ this.parentElement.label +"']");
                            if($(this.parentElement).find("option").length <= 1) {
                                $(this.parentElement).hide();
                            }
                            optgroup.append(this);
                        }
                    }
                    else {
                        if (i < lastIndex) {
                            optgroup = hiddenField.$field().find("optgroup[label='"+ this.parentElement.label +"']");
                            if($(this.parentElement).find("option").length <= 1) {
                                $(this.parentElement).hide();
                            }
                            optgroup.append(this);
                        }
                    }
                });
                tmpVal = field.getValue();
                hiddenField.$field().find('option').each(function() {
                    if (values[$(this).val()] === undefined) {
                        var attrList = self.$().data('attributes');
                        optgroup = field.$field().find("optgroup[label='" + this.parentElement.label + "']");
                        optgroup.append(this);
                        optgroup.show();
                        for(var x=0; x< attrList.length; x++) {
                            if(attrList[x]['label'] == this.parentElement.label){
                                options = optgroup.find("option");
                                optgroup.html(options.sort(function(a, b) {
                                    a = attrList[x]['value'][a.value]['position'];
                                    b = attrList[x]['value'][b.value]['position'];
                                    return a == b ? 0 : a < b ? -1 : 1;
                                }));
                            }
                        }
                    }
                });
                field.setValue(tmpVal);
            }
        }
    });
});