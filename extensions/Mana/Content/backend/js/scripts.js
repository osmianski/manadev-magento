/**
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // for better JS merging



Mana.define('Mana/Content/Folder/ListContainer', ['jquery', 'Mana/Admin/Container'],
function ($, Container)
{
    return Container.extend('Mana/Content/Folder/ListContainer', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    if (this.getChild('create-book')) this.getChild('create-book').on('click', this,
                        this.createBook);
                    if (this.getChild('create-feed')) this.getChild('create-feed').on('click', this,
                        this.createList);
                })
                .on('unload', this, function () {
                    if (this.getChild('create-book')) this.getChild('create-book').off('click', this,
                        this.createFeed);
                    if (this.getChild('create-feed')) this.getChild('create-feed').off('click', this,
                        this.createList);
                });
        },
        createBook: function () {
            setLocation(this.getUrl('create-book'));
        },
        createFeed: function () {
            setLocation(this.getUrl('create-feed'));
        }
    });
});


Mana.define('Mana/Content/Book/Tree', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Json', 'singleton:Mana/Core/Layout'],
function ($, Block, json, layout)
{
    return Block.extend('Mana/Content/Book/Tree', {
        _subscribeToHtmlEvents: function() {
            return this
                ._super()
                .on('bind', this, function () {
                    var options = this.getOptions();
                    if(options.core.data.id == null) {
                        options.core.data.id = "n" + this.createGuid();
                    }
                    var container = this.$container();
                    container.startingId = options.core.data.id;
                    var self = this;

                    options.core.check_callback = function (op, node, par, pos, more) {
                        if(more && more.dnd && (op === 'move_node' || op === 'copy_node')) {
                            if(
                                // Parent should not be moved/copied
                                par.id == "#" ||
                                // Only nodes without children (leaf nodes) are able to make a copy and reference
                                (((op === 'move_node' && container.triggerReference) || op === 'copy_node') && node.children.length > 0) ||
                                // Do not allow reference pages and referenced pages(original page that has reference) to have children
                                (op === 'move_node' || op === 'copy_node') && self.isTargetReferencePage(par)
                                ) {
                                return false;
                            }
                          }
                          return true;
                    };
                    this.$().jstree(options);
                })
                .on('unbind', this, function () {
                });
        },
        isTargetReferencePage: function (target) {
            var reference_pages = this.$container().reference_pages;
            for(var i in reference_pages) {
                if(reference_pages[i].id == target.id || reference_pages[i].reference_id == target.id) {
                    return true;
                }
            }
            return false;
        },
        $container: function() {
            return layout.getBlock('container');
        },
        getOptions: function() {
            var options = {
            };
            var dynamicOptions = this.$().data('options');
            if (dynamicOptions) {
                $.extend(true, options, json.decodeAttribute(dynamicOptions));
            }

            return options;
        },
        createGuid: function () {
            function s4() {
                return Math.floor(Math.random(0, 9) * 10).toString();
            }

            return s4() + s4() + s4() + s4() +
                s4() + s4() + s4() + s4();
        }
    });
});

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


Mana.define('Mana/Content/Book/TabContainer', ['jquery', 'Mana/Admin/Container', 'singleton:Mana/Core/Ajax', 'singleton:Mana/Core', 'singleton:Mana/Admin/Expression'],
function ($, Container, ajax, core, expression) {
    return Container.extend('Mana/Content/Book/TabContainer', {
        _init: function () {
            this._super();
            this._customInit();
        },
        _customInit: function () {
            this._changes = {
                // the keys are temporary IDs, format n123, "n" is to distinguish from IDs of
                //      database rows and "123" is some unique number
                // the values are structures like { 'fieldName': {value: '', isDefault: 0 or 1}}
                // one of the values will be 'parent_id' it is ID of parent database row or temporary
                // ID in n123 form which is the key in `created`
                created: {},
                // the keys are IDs of modified database rows
                // the values are structures like { 'fieldName': {value: '', isDefault: 0 or 1}}
                // one of the values will be 'parent_id' it is ID of parent database row
                modified: {},

                /*
                modified[1].title.value = "title ..."
                 */
                // the keys and values are IDs of deleted database rows like { '15': '15', '16: '16''}
                deleted: {}
            };
            this.errorPerRecord = {};
            this.triggerReference = false;
            this.useReferenceInsteadOfCopy = false;
            this.startingId = false;
            var self = this;
            $.each(this.$jsTreeElement().find("li.jstree-node"), function() {
                self._setNodeColor(false, $(this).attr("id"));
            });
        },
        setDefaultValuesToChanges: function () {
            if(this.getUrlParam('mana_content_book') == 'new') {
                var record = this.initChangesObj();

                record['title'] = {
                    value: this.getText('default-title'),
                    isDefault: 1
                };
                record['content'] = {
                    value: this.getText('default-content'),
                    isDefault: 1
                };

                this.onChangeTitle();
                if(this.getField('is_active')) {
                    this.getField('is_active').setValue('1');
                }
            }
        },
        _subscribeToHtmlEvents: function() {
            var self = this;
            var jsTreeChanged = function (e, data) {
                if(data.action == "select_node") {
                    var params = {};
                    params.changes = self._changes;
                    params.id = data.node.id;
                    params.form_key = FORM_KEY;

                    ajax.post(self.getUrl('load'), params, function (response) {
                        if (core.isString(response)) {
                            var activeTab = self.$varienTab().activeTab;
                            var reference_pages = self.reference_pages;
                            self.setContent(response);
                            self.reference_pages = reference_pages;
                            var msg = (self.errorPerRecord[params.id]) ? self.errorPerRecord[params.id] : "";
                            ajax.update({updates: {'#messages': msg}});

                            self.$varienTab().showTabContent(activeTab);
                            // For some reason, showTabContent() does not set tab class as active
                            // So, we do that here.
                            for(var i = 0 ; i < self.$varienTab().tabs.length; i++) {
                                if(self.$varienTab().tabs[i].id == activeTab.id) {
                                    $(self.$varienTab().tabs[i]).addClass('active');
                                    break;
                                }
                            }
                            if (self.$varienTab().activeTab.id == "content_tab") {
                                self.getField('title').$field().focus();
                            }

                            if(self.$().data('wysiwyg-enabled') == "enabled" && typeof wysiwygmf_content_content !== "undefined" && !self.isReferencePage()) {
                                // This line will reactivate wysiwyg `content` field.
                                wysiwygmf_content_content.setup("exact");
                            }

                            self._postAction("select");
                        }
                        else {
                            ajax.update(response);
                        }
                    });
                }
            };
            var jsTreeSaveState = function(e, data) {
                var params = {
                    state: self.$jsTree().get_state(),
                    form_key: FORM_KEY
                };
                ajax.post(self.getUrl('tree-save-state'), params);
            };

            var jsTreeMoveNode = function(e, data) {
                var record;
                var id = data.node.id;
                record = self.initChangesObj(id);

                record.parent_id = {
                    value: data.node.parent,
                    isDefault: 0
                };
                record.position = {
                    value: data.position,
                    isDefault: 0
                };

                $.each(self.$jsTree().get_children_dom(data.node.parent), function (index, childDOM) {
                    var child = self.initChangesObj(childDOM.id);
                    child.position = {
                        value: index,
                        isDefault: 0
                    };
                });
            };

            var jsTreeCopyNode = function (e, data) {
                if(self.triggerReference) {
                    self.triggerReference = false;
                    self.useReferenceInsteadOfCopy = true;
                }
                var id;
                if (self._isTemporaryId(data.original.id)) {
                    var obj = self.initChangesObj(data.original.id);
                    copyRecord(obj);
                } else {
                    id = data.original.id;
                    var params = {
                        form_key: FORM_KEY,
                        id: id
                    };
                    ajax.post(self.getUrl('getRecord'), params, function (response) {
                        copyRecord(response.data);
                    });
                }

                function copyRecord(obj) {
                    var record = $.extend({}, obj);
                    if(typeof record.reference_id !== "undefined" && (record.reference_id.value === null || record.reference_id.value == "0")) {
                        delete record.reference_id;
                    }
                    var copiedRecordId = (typeof record.reference_id !== "undefined") ? record.reference_id : record.id;
                    delete record.id;
                    record = self.createNewRecord(record);
                    record.parent_id = {value: data.parent, isDefault: 1};
                    record.position = {value: data.position, isDefault: 1};
                    if (self.useReferenceInsteadOfCopy) {
                        self.useReferenceInsteadOfCopy = false;
                        record.reference_id = copiedRecordId;
                        self.reference_pages.push({id: record.id.value, reference_id: record.reference_id.value});
                    }
                    self.$jsTree().set_id(data.node.id, record.id.value);
                    self._setNodeColor("green", record.id.value);
                    self.$jsTree().deselect_all();
                    self.$jsTree().select_node(record.id.value);
                }
            };

            var jsTreeDndMove = function(e, data) {
                self.triggerReference = data.event.altKey;
            };

            var cancelSubmit = function(evt) {
                var evt = (evt) ? evt : ((event) ? event : null);
                if (evt.keyCode == 13) {
                    return false;
                }
            };

            return this._super()
                .on('bind', this, function() {
                    this.$jsTreeElement().on('changed.jstree', jsTreeChanged);
                    this.$jsTreeElement().on('close_node.jstree', jsTreeSaveState);
                    this.$jsTreeElement().on('open_node.jstree', jsTreeSaveState);
                    this.$jsTreeElement().on('move_node.jstree', jsTreeMoveNode);
                    this.$jsTreeElement().on('copy_node.jstree', jsTreeCopyNode);
                    this.$().find("#mf_content_title").on('keypress', cancelSubmit);
                    $(document).on('dnd_move.vakata', jsTreeDndMove);
                    this.setDeleteButtonText();
                    this.reference_pages = this.$().data('reference-pages');
                })
                .on('unbind', this, function() {
                    this.$jsTreeElement().off('changed.jstree', jsTreeChanged);
                    this.$jsTreeElement().off('close_node.jstree', jsTreeSaveState);
                    this.$jsTreeElement().off('open_node.jstree', jsTreeSaveState);
                    this.$jsTreeElement().off('move_node.jstree', jsTreeMoveNode);
                    this.$jsTreeElement().off('copy_node.jstree', jsTreeCopyNode);
                    this.$().find("#mf_content_title").off('keypress', cancelSubmit);
                    $(document).off('dnd_move.vakata', jsTreeDndMove);
                })
        },
        isOnRootNode: function() {
            return this.getUrlParam('id') === this.getCurrentId();
        },
        isReferencePage: function () {
            var reference_id = this.getField('reference_id').getValue();
            return reference_id !== "";
        },
        disableFieldsIfReferencePage: function () {
            if(this.isReferencePage()) {
                var self = this;
                $.each(this.getFields(), function (fieldName) {
                    var field = self.getField(fieldName);
                    // if the field is one of the watchedClasses, bind fieldChanged event
                    field.disable();
                    field.$useDefault().parent().hide();
                    if(typeof field.$picker !== "undefined") {
                        field.$picker().hide();
                    }
                });
            }
        },
        setDeleteButtonText: function() {
            if(this.isOnRootNode() && !this.getUrlParam('store')) {
                this.$().find("button.mb-container-delete span")[0].innerHTML = this.getText('delete-whole-page');
            }
        },
        deleteNode: function () {
            var confirmText = (this.isOnRootNode()) ? this.getText('delete-confirm-root') : this.getText('delete-confirm');
            if(confirm(confirmText)) {
                var id = this.getCurrentId();

                for(var x in this.reference_pages) {
                    if(id == this.reference_pages[x].reference_id) {
                        var reference_page = this.reference_pages[x];
                        if (this._isTemporaryId(reference_page.id)) {
                            delete this._changes.created[reference_page.id];
                            this.$jsTree().delete_node(reference_page.id);
                        } else {
                            this._changes.deleted[reference_page.id] = reference_page.id;
                            this._setNodeColor("red", reference_page.id);
                        }
                    }
                }

                if (this._isTemporaryId(id)) {
                    delete this._changes.created[id];
                    this.$jsTree().delete_node(id);
                    this.$jsTree().select_node(this.getUrlParam('id'));
                } else {
                    delete this._changes.modified[id];
                    this._changes.deleted[id] = id;
                    this._setNodeColor("red");
                    if(this.isOnRootNode()) {
                        this.saveAndClose();
                    }
                }
                this._postAction("delete");
            }
        },
        _initOriginalFields: function (reset) {
            if (reset || typeof this._originalFields === "undefined") {
                this._originalFields = {};
            }
            var id = this.getCurrentId();
            if(typeof this._originalFields[id] === "undefined") {
                this._originalFields[id] = {};
                for (var i in this.getFields()) {
                    this._originalFields[id][i] = {};
                    this._originalFields[id][i].value = this.getField(i).getValue();
                    this._originalFields[id][i].useDefault = this.getField(i).useDefault();
                }
                return this._originalFields[id];
            }
            return false;
        },
        goToOriginalPage: function () {
            var reference_id = this.getField('reference_id').getValue();
            this.$jsTree().deselect_all();
            this.$jsTree().select_node(reference_id);
        },
        _subscribeToBlockEvents: function () {
            var watchedClasses = ['Mana_Admin_Field_Text', 'Mana_Admin_Field_TextArea', 'Mana_Admin_Field_Select', 'Mana_Content_Wysiwyg'];
            return this
                ._super()
                .on('load', this, function () {
                    var self = this;
                    $.each(this.getFields(), function( fieldName ) {
                        var field = self.getField(fieldName);
                        // if the field is one of the watchedClasses, bind fieldChanged event
                        if ($.inArray(field.constructor.name, watchedClasses) !== -1) {
                            if(field) field.on('change', self, self.fieldChanged);
                        }
                    });

                    if (this.getChild('create')) this.getChild('create').on('click', this, this.createChildNode);
                    if (this.getChild('delete')) this.getChild('delete').on('click', this, this.deleteNode);
                    if (this.getChild('goToOriginal')) this.getChild('goToOriginal').on('click', this, this.goToOriginalPage);
                    this.setDefaultValuesToChanges();
                    this._initOriginalFields(false);
                    this.disableFieldsIfReferencePage();
                })
                .on('unload', this, function () {
                    var that = this;
                    $.each(this.getFields(), function (fieldName) {
                        var field = that.getField(fieldName);

                        // if the field is one of the watchedClasses, bind fieldChanged event
                        if ($.inArray(field.constructor.name, watchedClasses) !== -1) {
                            if(field) field.off('change', that, that.fieldChanged);
                        }
                    });

                    if (this.getChild('create')) this.getChild('create').off('click', this, this.createChildNode);
                    if (this.getChild('delete')) this.getChild('delete').off('click', this, this.deleteNode);
                    if (this.getChild('goToOriginal')) this.getChild('goToOriginal').off('click', this, this.goToOriginalPage);
                });
        },
        _afterSave: function(response) {
            var newIds = response.newId;
            for (var id in this.errorPerRecord) {
                this.$jsTree().set_icon(id, true);
            }
            for(var tmpId in newIds) {
                this.$jsTree().set_id(tmpId, newIds[tmpId]);
                for(var i in this.reference_pages) {
                    if(this.reference_pages[i].id == tmpId) {
                        this.reference_pages[i].id = newIds[tmpId];
                    }
                    if (this.reference_pages[i].reference_id == tmpId) {
                        this.reference_pages[i].reference_id = newIds[tmpId];
                    }
                }
            }

            for(var id in this._changes.deleted) {
                if(this.getCurrentId() == id) {
                    this.$jsTree().select_node(this.getUrlParam('id'));
                }
                this.$jsTree().delete_node(id);
            }

            this._customInit();
            this._initOriginalFields(true);
        },
        _onSaveFailed: function(response) {
            this.errorPerRecord = response.errorPerRecord;
            for(var id in this.errorPerRecord) {
                this.$jsTree().set_icon(id, this.getUrl('tree-icon-error'));
            }
            for (var key in this.errorPerRecord) {
                if(this.getCurrentId() != key) {
                    this.$jsTree().deselect_all();
                    this.$jsTree().select_node(key);
                }
                break;
            }
        },
        getPostParams: function() {
            return {
                form_key: FORM_KEY,
                changes: this._changes,
                selectedRecord: this.getCurrentId(),
                rootPageId: this.getUrlParam('id')
            };
        },
        createNewRecord: function (recordData) {
            recordData = (recordData) ? recordData : {};
            recordData.id = (recordData.id) ? recordData.id : {value: "n" + this.createGuid(), isDefault: 1};
            var record = this.initChangesObj(recordData.id.value);
            record['title'] = {
                value: this.getText('default-title'),
                isDefault: 1
            };
            record['url_key'] = {
                value: expression.seoify(this.getText('default-title')),
                isDefault: 1
            };
            record['content'] = {
                value: this.getText('default-content'),
                isDefault: 1
            };
            record['parent_id'] = {
                value: this.getCurrentId(),
                isDefault: 1
            };
            return $.extend(record, recordData);
        },
        createChildNode: function() {
            var record = this.createNewRecord();
            var node = {
                id: record.id.value,
                text: record.title.value
            };
            var obj = this.$jsTree().create_node(this.getCurrentId(), node);
            this.$jsTree().deselect_all();
            this.$jsTree().select_node(obj);
            this._setNodeColor("green");
        },
        createGuid: function () {
            function s4() {
                return Math.floor(Math.random(0, 9) * 10).toString();
            }
            return s4() + s4() + s4() + s4() +
                s4() + s4() + s4() + s4();
        },
        $jsTree: function() {
            return this.$jsTreeElement().jstree(true);
        },
        $jsTreeElement: function() {
            return $("#tree");
        },
        $varienTab: function() {
            return window[this.$().data('tab-id') + 'JsTabs'];
        },
        getCurrentId: function() {
            if(typeof this.$jsTree().get_selected()[0] === "undefined") {
                return this.startingId;
            } else {
                return this.$jsTree().get_selected()[0];
            }
        },
        initChangesObj: function (id) {
            if(id === undefined) {
                id = this.getCurrentId();
            }
            if(this._isTemporaryId(id)) {
                if (!this._changes.created[id]) {
                    this._changes.created[id] = {};
                    this._changes.created[id].related_products = [];
                }
                return this._changes.created[id];
            } else {
                if (!this._changes.modified[id]) {
                    this._changes.modified[id] = {};
                    this._changes.modified[id].related_products = [];
                }
                return this._changes.modified[id];
            }
        },
        getUrlParam: function(param) {
            var vars = window.location.toString().split('/');
            for(var i = 0; i < vars.length; i++) {
                if(vars[i] == param) {
                    return vars[i+1];
                }
            }
            return false;
        },
        setToBlackIfNoChanges: function () {
            var obj = this.initChangesObj();

            if (typeof this._originalFields !== "undefined" && !this._isTemporaryId(this.getCurrentId())) {
                for (var i in obj) {
                    var originalField = this._originalFields[this.getCurrentId()][i];
                    if (typeof originalField !== "undefined" &&
                        originalField.value === obj[i].value &&
                        originalField.useDefault === obj[i].isDefault) {
                        if (typeof this._changes.modified[this.getCurrentId()][i] !== "undefined") {
                            delete this._changes.modified[this.getCurrentId()][i];
                        }
                    }
                }
                if(typeof obj.related_products !== "undefined" && obj.related_products.length == 0) {
                    delete obj.related_products;
                }
                var count = Object.keys(this._changes.modified[this.getCurrentId()]).length;
                if (count == 0) {
                    delete this._changes.modified[this.getCurrentId()];
                    this._setNodeColor("black");
                }
                if (typeof obj.related_products !== "undefined") {
                    obj.related_products = [];
                }
            }
        },
        fieldChanged: function (e) {
            var strField = e.target.getName();
            var field = this.getField(strField);
            this.initChangesObj()[strField] = {
                value: field.getValue(),
                isDefault: field.useDefault()
            };
            function underscoreToCamelCase(string) {
                string = string.replace('_', ' ');
                var words = string.split(' ');
                for(var i=0; i < words.length; i++) {
                    words[i] = words[i].charAt(0).toUpperCase() + words[i].slice(1).toLowerCase()
                }
                return words.join('');
            }
            var fieldChangeFunction = "onChange" + underscoreToCamelCase(strField);
            if(typeof this[fieldChangeFunction] === "function") {
                this[fieldChangeFunction]();
            }
            this._postAction("modify");
            this.setToBlackIfNoChanges();
        },
        onChangeUrlKey: function() {
            var field = this.getField('url_key');
            var title = this.getField('title').getValue();
            var url_key = expression.seoify(title);
            if(typeof field !== "undefined" && field.useDefault()) {
                field.setValue(url_key);
            }
            if(typeof field === "undefined") {
                this.initChangesObj()['url_key'] = {
                    value: url_key,
                    isDefault: 1
                };
                this.setToBlackIfNoChanges();
            }
        },
        onChangeTitle: function() {
            var field = this.getField('title');
            var obj = this.getCurrentId();
            var title = field.getValue();
            var no_of_char = parseInt(this.$().data('visible-title-char'));
            $("#" + obj).attr('title', title);
            if (title.length > no_of_char) {
                title = title.substring(0, no_of_char);
                title += "...";
            }
            this.onChangeUrlKey();
            this.onChangeMetaTitle();

            this.$jsTree().rename_node(obj, title);
            for(var i in this.reference_pages) {
                if(this.reference_pages[i].reference_id == obj) {
                    this.$jsTree().rename_node(this.reference_pages[i], title);
                }
            }
            this.$().find("div.content-header tr:first h3.head-empty")[0].innerHTML = field.getValue() + " - Book";
        },
        onChangeMetaTitle: function() {
            var field = this.getField('meta_title');
            if (typeof field !== "undefined" && field.useDefault()) {
                field.setValue(this.getField('title').getValue());
            }
        },
        onChangeTags: function() {
            var field = this.getField('meta_keywords');
            if (typeof field !== "undefined" && field.useDefault()) {
                field.setValue(this.getField('tags').getValue());
            }
        },
        onChangeMetaKeywords: function() {
            this.onChangeTags();
        },
        _isTemporaryId: function(id) {
            return id.charAt(0) === "n"
        },
        _setNodeColor: function (color, id) {
            id = (id === undefined) ? this.getCurrentId(): id;
            var li_attr = this.$jsTree().get_node(id).li_attr;
            var classPrefix = "node-color-";
            if (!li_attr.class) {
                li_attr.class = [];
            }

            for(var i = 0; i < li_attr.class.length; i++) {
                if(li_attr.class[i].startsWith(classPrefix)) {
                    $("#" + id).removeClass(li_attr.class[i]);
                    li_attr.class.splice(i, 1);
                }
            }

            if(color) {
                var colorClass = classPrefix + color;
                li_attr.class.push(colorClass);
                $("#" + id).addClass(colorClass);
            }
        },
        _postAction: function(action) {
            switch(action) {
                case "delete":
                    if (this.getText('save-mode') !== "all") {
                        this.save();
                    }
                    break;
                case "modify":
                    if(!this._isTemporaryId(this.getCurrentId()) && this._changes.deleted[this.getCurrentId()] === undefined) {
                        this._setNodeColor("blue");
                    }
                    if(this.getText('save-mode') === "field") {
                        this.save();
                    }
                    break;
                case "select":
                    if(this.getText('save-mode') === "page") {
                        this.save();
                    }
                    break;
            }
        }
    });
});

Mana.define('Mana/Content/Book/TabContainer/Global',
['jquery', 'Mana/Content/Book/TabContainer'],
function ($, TabContainer) {
    return TabContainer.extend('Mana/Content/Book/TabContainer/Global', {
    });
});

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
            if(typeof field !== "undefined" && field.useDefault()) {
                if (this.getJsonData('global-is-custom', 'url_key')) {
                    field.setValue(this.getJsonData('global', 'url_key'));
                } else {
                    var title = this.getField('title').getValue();
                    var url_key = expression.seoify(title);
                    field.setValue(url_key);
                }
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

//# sourceMappingURL=content.js.map