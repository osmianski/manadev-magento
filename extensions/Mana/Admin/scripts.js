/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('Mana/Admin/Action', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/Admin/Action', {
        _init: function() {
            this._super();
            this.setIsSelfContained(true);
        },
        //region Event handlers
        _subscribeToHtmlEvents:function () {
            var self = this;
            function _raiseClick () {
                if (!self.$().hasClass('disabled')) {
                    self.trigger('click');
                }
            }

            return this
                ._super()
                .on('bind', this, function () {
                    $('.mb-'+this.getId()).on('click', _raiseClick);
                })
                .on('unbind', this, function () {
                    $('.mb-' + this.getId()).off('click', _raiseClick);
                });
        },
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('readonly-changed', this, this.onReadonlyChanged);
        },
        onReadonlyChanged: function (e) {
            if (!this.$().data('readonly-action')) {
                if (e.value) {
                    this.$().addClass('disabled');
                }
                else {
                    this.$().removeClass('disabled');
                }
            }
        }

        //endregion
    });
});
Mana.define('Mana/Admin/Grid/Cell', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/Admin/Grid/Cell', {
        _init: function () {
            this._super();
            this.setIsSelfContained(true);
        },
        getRow: function () {
            return this.getParent();
        },
        getGrid: function () {
            return this.getRow().getGrid();
        },
        getColumn: function () {
            return this.getGrid().getColumn($.inArray(this, this.getRow().getChildren()));
        },
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('readonly-changed', this, this.onReadonlyChanged);
        },
        onReadonlyChanged: function (e) {
        }
    });
});
Mana.define('Mana/Admin/Grid/Column', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/Admin/Grid/Column', {
        _init:function () {
            this._super();
            this.setIsSelfContained(true);
        },
        getColumnName: function() {
            return this.getAlias().replace(/-/g, '_');
        }
    });
});
Mana.define('Mana/Admin/Grid/Row', ['jquery', 'Mana/Core/Block', 'Mana/Admin/Grid/Cell', 'singleton:Mana/Core/UrlTemplate'],
function ($, Block, Cell, urlTemplate)
{
    return Block.extend('Mana/Admin/Grid/Row', {
        _init:function () {
            this._super();
            this._url = '';
            this.setIsSelfContained(true);
        },
        getGrid: function() {
            return this.getParent();
        },
        getCells:function () {
            return this.getChildren(function (index, child) {
                return child instanceof Cell;
            });
        },
        getCell:function (index) {
            var columnIndex = -1;
            return this.getChild(function (i, child) {
                return child instanceof Cell && ++columnIndex == index;
            });
        },
        getRowId:function () {
            return this.$().data('row-id');
        },
        getUrl: function () {
            if (!this._url) {
                this._url = urlTemplate.decodeAttribute(this.$().data('url'));
            }
            return this._url;
        },
        setUrl: function (value) {
            this._url = value;
            return this;
        },
        _subscribeToHtmlEvents: function () {
            var self = this;
            function _processClick(e) { self._onClick(e); }

            return this
                ._super()
                .on('bind', this, function () {
                    this.$().on('click', _processClick);
                })
                .on('unbind', this, function () {
                    this.$().off('click', _processClick);
                });
        },
        _onClick: function(e) {
            if (['a', 'input', 'select', 'option'].indexOf(e.target.tagName.toLowerCase()) != -1) {
                return;
            }

            if (this.getUrl()) {
                setLocation(this.getUrl());
            }
        }
    });
});

Mana.define('Mana/Admin/Grid/Cell/Text', ['jquery', 'Mana/Admin/Grid/Cell'], function ($, Cell) {
    return Cell.extend('Mana/Admin/Grid/Cell/Text', {});
});

Mana.define('Mana/Admin/Grid/Cell/Options', ['jquery', 'Mana/Admin/Grid/Cell'], function ($, Cell) {
    return Cell.extend('Mana/Admin/Grid/Cell/Options', {});
});
Mana.define('Mana/Admin/Grid/Cell/Datetime', ['jquery', 'Mana/Admin/Grid/Cell'], function ($, Cell) {
    return Cell.extend('Mana/Admin/Grid/Cell/Datetime', {});
});
Mana.define('Mana/Admin/Grid/Cell/Store', ['jquery', 'Mana/Admin/Grid/Cell'], function ($, Cell) {
    return Cell.extend('Mana/Admin/Grid/Cell/Store', {});
});
Mana.define('Mana/Admin/Grid/Cell/Select', ['jquery', 'Mana/Admin/Grid/Cell'], function ($, Cell) {
    return Cell.extend('Mana/Admin/Grid/Cell/Select', {
        _subscribeToHtmlEvents: function() {
            var self = this;
            function _raiseChange() {
                return self.onChange();
            }

            return this
                ._super()
                .on('bind', this, function() {
                    this.$input().on('change', _raiseChange);
                })
                .on('unbind', this, function () {
                    this.$input().off('change', _raiseChange);
                });
        },
        $input: function() {
            return this.$().find('select');
        },
        onChange: function() {
            this.getGrid().setCellValue(this, { value: this.$input().val() });
        },
        onReadonlyChanged: function (e) {
            if (e.value) {
                this.$input().attr('disabled', true).addClass('disabled');
            }
            else {
                this.$input().removeAttr('disabled').removeClass('disabled');
            }
        }
    });
});
Mana.define('Mana/Admin/Grid/Cell/Input', ['jquery', 'Mana/Admin/Grid/Cell'], function ($, Cell) {
    return Cell.extend('Mana/Admin/Grid/Cell/Input', {
        _subscribeToHtmlEvents:function () {
            var self = this;

            function _raiseChange() {
                return self.onChange();
            }

            return this
                ._super()
                .on('bind', this, function () {
                    this.$input().on('change', _raiseChange);
                })
                .on('unbind', this, function () {
                    this.$input().off('change', _raiseChange);
                });
        },
        $input:function () {
            return this.$().find('input');
        },
        onChange:function () {
            this.getGrid().setCellValue(this, { value:this.$input().val() });
        },
        onReadonlyChanged: function (e) {
            if (e.value) {
                this.$input().attr('disabled', true).addClass('disabled');
            }
            else {
                this.$input().removeAttr('disabled').removeClass('disabled');
            }
        }
    });
});
Mana.define('Mana/Admin/Grid/Cell/Checkbox', ['jquery', 'Mana/Admin/Grid/Cell'], function ($, Cell) {
    return Cell.extend('Mana/Admin/Grid/Cell/Checkbox', {
        _subscribeToHtmlEvents:function () {
            var self = this;

            function _raiseClick() {
                return self.onClick();
            }

            return this
                ._super()
                .on('bind', this, function () {
                    this.$input().on('click', _raiseClick);
                })
                .on('unbind', this, function () {
                    this.$input().off('click', _raiseClick);
                });
        },
        $input:function () {
            return this.$().find('input');
        },
        onClick:function () {
            this.getGrid().setCellValue(this, { value:this.$input().attr('checked') == 'checked' ? 1 : 0 });
        },
        onReadonlyChanged: function (e) {
            if (e.value) {
                this.$input().attr('disabled', true).addClass('disabled');
            }
            else {
                this.$input().removeAttr('disabled').removeClass('disabled');
            }
        }
    });
});
Mana.define('Mana/Admin/Grid/Cell/Massaction', ['jquery', 'Mana/Admin/Grid/Cell/Checkbox'], function ($, Checkbox) {
    return Checkbox.extend('Mana/Admin/Grid/Cell/Massaction', {
    });
});
Mana.define('Mana/Admin/Grid', ['jquery', 'Mana/Core/Block', 'Mana/Admin/Grid/Row',
    'Mana/Admin/Grid/Column', 'singleton:Mana/Core',
    'singleton:Mana/Core/Ajax', 'singleton:Mana/Core/Json', 'singleton:Mana/Core/Config',
    'singleton:Mana/Core/Base64', 'singleton:Mana/Core/UrlTemplate'],
    function ($, Block, Row, Column, core, ajax, json, config, base64, urlTemplate, undefined) {
        var RewrittenVarienGrid = Class.create(varienGrid, {
            reload: function (url) {
                if (!this.reloadParams) {
                    this.reloadParams = {form_key: FORM_KEY};
                }
                else {
                    this.reloadParams.form_key = FORM_KEY;
                }
                url = url || this.url;

                this._block._updateReloadParams();
                var self = this;
                ajax.post(url, this.reloadParams || {}, function (response) {
                    if (core.isString(response)) {
                        self._block.setContent(response);
                    }
                    else {
                        ajax.update(response);
                    }
                });
            }
        });

        return Block.extend('Mana/Admin/Grid', {
            _init: function () {
                this._super();
                this._varienGrid = null;
                this._url = '';
                this._readonly = false;
                this._edit = {
                    pending: {},
                    saved: {},
                    deleted: {}
                };
                this._raw = undefined;
            },

            getUrl: function () {
                if (!this._url) {
                    this._url = urlTemplate.decodeAttribute(this.$().data('url'));
                }
                return this._url;
            },
            setUrl: function (value) {
                this._url = value;
                return this;
            },
            getEdit: function () {
                return this._edit;
            },
            getRaw: function () {
                return this._raw;
            },
            setEdit: function (value) {
                this._edit = value;
                return this;
            },

            //region Event handlers
            _subscribeToHtmlEvents: function () {
                var self = this;

                function _setPage() {
                    self._varienGrid.setPage($(this).data('page'));
                    return false;
                }

                function _inputPage() {
                    self._varienGrid.inputPage(this, $(this).data('page'));
                }

                function _loadByElement() {
                    self._varienGrid.loadByElement(this);
                }

                function _useDefault() {
                    self._edit.useDefault = this.checked;
                    self.trigger('readonly-changed', {value: self._edit.useDefault}, false, true);
                }

                return this
                    ._super()
                    .on('bind', this, function () {
                        //noinspection JSPotentiallyInvalidConstructorUsage
                        self._varienGrid = new RewrittenVarienGrid(this.getElement().id,
                            this.getUrl(), 'page', 'sort', 'dir', 'filter');
                        self._varienGrid.useAjax = true;
                        self._varienGrid._block = this;

                        var edit = self.$().data('edit');
                        if (edit) {
                            this._edit = json.decodeAttribute(edit);
                        }

                        var raw = self.$().data('raw');
                        if (raw) {
                            this._raw = json.decodeAttribute(raw);
                        }

                        if (self._edit.useDefault === undefined) {
                            self._edit.useDefault = this.$().data('readonly') ? true : false;
                        }
                        self.trigger('readonly-changed', {value: self._edit.useDefault}, false, true);

                        this.$().find('.pager .previous, .pager .next').on('click', _setPage);
                        this.$().find('.pager .input-text.page').on('keypress', _inputPage);
                        this.$().find('.pager .limit').on('change', _loadByElement);
                        this.$().find('input.m-default').on('click', _useDefault);
                    })
                    .on('unbind', this, function () {
                        this.$().find('.pager .previous, .pager .next').off('click', _setPage);
                        this.$().find('.pager .input-text.page').off('keypress', _inputPage);
                        this.$().find('.pager .limit').off('change', _loadByElement);
                        this.$().find('.input.m-default').off('click', _useDefault);
                        this._varienGrid = null;
                    });
            },
            _subscribeToBlockEvents: function () {
                var self = this;
                return this
                    ._super()
                    .on('load', this, function () {
                        if (this.getChild('search')) this.getChild('search').on('click', this, this.search);
                        if (this.getChild('reset')) this.getChild('reset').on('click', this, this.reset);
                        if (this.getChild('add')) this.getChild('add').on('click', this, this.addRow);
                        if (this.getChild('remove')) this.getChild('remove').on('click', this, this.removeRow);
                        this.on('post', this, this.post);
                    })
                    .on('unload', this, function () {
                        if (this.getChild('search')) this.getChild('search').off('click', this, this.search);
                        if (this.getChild('reset')) this.getChild('reset').off('click', this, this.reset);
                        if (this.getChild('add')) this.getChild('add').off('click', this, this.addRow);
                        if (this.getChild('remove')) this.getChild('remove').off('click', this, this.removeRow);
                        this.off('post', this, this.post);
                    });
            },
            search: function () {
                this._varienGrid.doFilter();
                return this;
            },
            reset: function () {
                this._varienGrid.resetFilter();
                return this;
            },
            addRow: function () {
                this._call('add');
                return this;
            },
            removeRow: function () {
                this._call('remove');
                return this;
            },
            post: function (e) {
                if (e.target.getId() == 'container') {
                    this._updateReloadParams();
                    e.result.push({name: this.getAlias(), value: json.stringify(this._varienGrid.reloadParams)});
                }
            },
            //endregion
            _call: function (action, args) {
                var gridUrl = this._varienGrid.url;

                this._varienGrid.addVarToUrl('action', action);
                if (args) {
                    this._varienGrid.addVarToUrl('args', base64.encode(json.stringify(args)));
                }

                var url = this._varienGrid.url;
                this._varienGrid.url = gridUrl;

                this._varienGrid.reload(url);
            },
            _updateReloadParams: function () {
                if (!this._varienGrid.reloadParams) {
                    this._varienGrid.reloadParams = {};
                }
                this._varienGrid.reloadParams['edit'] = json.stringify($.extend(
                    { sessionId: config.getData('editSessionId') },
                    this.getEdit()
                ));

                if (this.getRaw()) {
                    this._varienGrid.reloadParams['raw'] = json.stringify(this.getRaw());
                }
            },
            getRows: function () {
                return this.getChildren(function (index, child) {
                    return child instanceof Row;
                });
            },
            getColumns: function () {
                return this.getChildren(function (index, child) {
                    return child instanceof Column;
                });
            },
            getColumn: function (index) {
                var columnIndex = -1;
                return this.getChild(function (i, child) {
                    return child instanceof Column && ++columnIndex == index;
                });
            },
            setCellValue: function (cell, compositeValue) {
                var id = cell.getRow().getRowId();
                var column = cell.getColumn().getColumnName();
                if (!this._edit.pending[id]) {
                    this._edit.pending[id] = {};
                }
                if (!this._edit.pending[id][column]) {
                    this._edit.pending[id][column] = {};
                }
                $.extend(this._edit.pending[id][column], compositeValue);

                return this;
            }
        });
    });
Mana.define('Mana/Admin/Tab', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Layout'], function ($, Block, layout) {
    return Block.extend('Mana/Admin/Tab', {
//        trigger: function(name, e, bubble, propagate) {
//            this._super(name, e, bubble, propagate);
//            var container;
//            if (bubble && (container = layout.getBlock('container'))) {
//                container.trigger(name, e, bubble, propagate);
//            }
//            return e.result;
//        }
    });
});
Mana.define('Mana/Admin/Container', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/UrlTemplate',
    'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Ajax', 'singleton:Mana/Core/Config', 'singleton:Mana/Core'],
function ($, Block, urlTemplate, layout, ajax, config, core, undefined)
{
    return Block.extend('Mana/Admin/Container', {
        _init: function () {
            this._super();
            this._url = {}
            this._messages = {};
        },
        getUrl:function (key) {
            if (this._url[key] === undefined) {
                this._url[key] = urlTemplate.decodeAttribute($(this.getElement()).data(key + '-url'));
            }
            return this._url[key];
        },
        setUrl:function (key, value) {
            this._url[key] = value;
            return this;
        },

        _subscribeToHtmlEvents: function () {
            var self = this;

            function _hideMessage(e) {
                self._hideMessage(this, e.data.messageKey);
            }

            return this
                ._super()
                .on('bind', this, function () {
                    $.each(self._messages, function(messageKey) {
                        $('.' + messageKey + '-message').on('click', {messageKey: messageKey}, _hideMessage);
                    });
                })
                .on('unbind', this, function () {
                    $.each(self._messages, function (messageKey) {
                        $('.' + messageKey + '-message').on('click', _hideMessage);
                    });
                });
        },
        _subscribeToBlockEvents:function () {
            return this
                ._super()
                .on('load', this, function () {
                    this._fields = undefined;
                    if (this.getChild('close')) this.getChild('close').on('click', this, this.close);
                    if (this.getChild('apply')) this.getChild('apply').on('click', this, this.save);
                    if (this.getChild('save')) this.getChild('save').on('click', this, this.saveAndClose);
                })
                .on('unload', this, function () {
                    if (this.getChild('close')) this.getChild('close').off('click', this, this.close);
                    if (this.getChild('apply')) this.getChild('apply').off('click', this, this.save);
                    if (this.getChild('save')) this.getChild('save').off('click', this, this.saveAndClose);
                });
        },
        getField: function (name) {
            if (this._fields === undefined) {
                this._fields = layout.getPageBlock().trigger('collect', { fields: true, result: {} }, false, true);
            }
            return this._fields[name];
        },
        updateFromJson: function(fieldName, attributeName, jsonFieldName) {
            if (jsonFieldName === undefined) {
                jsonFieldName = fieldName;
            }
            var field = this.getField(fieldName);
            if (field.useDefault()) {
                field.setValue(this.getJsonData(attributeName, jsonFieldName));
            }
        },
        save: function(callback) {
            var params = this.getPostParams();

            ajax.post(this.getUrl('save'), params, function(response) {
                ajax.update(response);
                //noinspection JSUnresolvedVariable
                if (core.isFunction(callback) && !response.failed) {
                    callback.call();
                }
            });
        },
        getPostParams: function() {
            var params = [{name: 'form_key', value: FORM_KEY}];
            if (config.getData('editSessionId') !== undefined) {
                params.push({name: 'sessionId', value: config.getData('editSessionId')});
            }
            return layout.getPageBlock().trigger('post', { target: this, result: params}, false, true);
        },
        close: function() {
            window.location.href = this.getUrl('close');
        },
        saveAndClose: function() {
            var self = this;
            this.save(function() {
                self.close();
            });
        },
        _hideMessage: function (a, messageKey) {
            //noinspection JSCheckFunctionSignatures
            var $li = $(a).parent();
            $li.hide();
            //noinspection JSCheckFunctionSignatures
            for (var $parent = $li.parent(); $parent.length && $parent[0].id != 'messages'; $parent = $parent.parent()) {
                if ($parent.children(':visible').length) {
                    break;
                }
                $parent.hide();
            }
            ajax.post(this.getUrl('hide-' + messageKey.replace(/_/g, '-') + '-message'), [
                {name: 'form_key', value: FORM_KEY}
            ]);
        }
    });
});
Mana.define('Mana/Admin/Field', ['jquery', 'Mana/Core/Block'], function ($, Block, undefined) {
    return Block.extend('Mana/Admin/Field', {
        _subscribeToHtmlEvents: function () {
            var self = this;

            function _raiseUseDefaultClick() {
                return self.onUseDefaultClick();
            }

            return this
                ._super()
                .on('bind', this, function () {
                    this.$useDefault().on('click', _raiseUseDefaultClick);
                })
                .on('unbind', this, function () {
                    this.$useDefault().off('click', _raiseUseDefaultClick);
                });
        },
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    this.on('collect', this, this.onCollectFields);
                })
                .on('unload', this, function () {
                    this.off('collect', this, this.onCollectFields);
                });
        },
        $useDefault: function () {
            return this.$().find('td.use-default input.m-default');
        },
        $form: function() {
            return $(this.$().parents('form')[0]);
        },
        onUseDefaultClick: function () {
            if (this.$useDefault()[0].checked) {
                this.disable();
            }
            else {
                this.enable();
            }
            this.changed();
        },
        onCollectFields: function(e) {
            if (e.fields) {
                e.result[this.getName()] = this;
            }
        },
        disable: function() {
            throw 'Not implemented';
        },
        enable: function() {
            throw 'Not implemented';
        },
        getName: function() {
            return this.$()[0].id.substr(this.$form()[0].id.length + '_tr_'.length);
        },
        getValue: function() {
            throw 'Not implemented';
        },
        getText: function () {
            throw 'Not implemented';
        },
        setValue: function(value) {
            throw 'Not implemented';
        },
        useDefault: function() {
            return this.$useDefault().length && this.$useDefault()[0].checked;
        },
        changed: function () {
            if (this._changed === undefined) {
                this._changed = true;

                var newValue = this.trigger('change');
                if (newValue !== undefined) {
                    this.setValue(newValue);
                }

                delete this._changed;
            }
        }
    });
});
Mana.define('Mana/Admin/Field/Select', ['jquery', 'Mana/Admin/Field'], function($, Field) {
    return Field.extend('Mana/Admin/Field/Select', {
        _subscribeToHtmlEvents: function () {
            var self = this;

            function _changed() {
                self.changed();
            }
            return this
                ._super()
                .on('bind', this, function () {
                    this.$field().on('change', _changed);
                })
                .on('unbind', this, function () {
                    this.$field().off('change', _changed);
                });
        },
        $field: function() {
            return this.$().find('td.value:first select');
        },
        disable: function() {
            this.$field().attr('disabled', true).addClass('disabled');
        },
        enable: function() {
            this.$field().removeAttr('disabled').removeClass('disabled').focus();
        },
        getValue: function() {
            return this.$field().val();
        },
        getText: function () {
            return this.$().find('td.value:first select :selected').text();
        },
        setValue: function(value) {
            if (this.$field().val() == value) {
                return;
            }
            this.$field()
                .val(value)
                .trigger('change');
        }
    });
});

Mana.define('Mana/Admin/Field/Text', ['jquery', 'Mana/Admin/Field'], function($, Field) {
    return Field.extend('Mana/Admin/Field/Text', {
        _subscribeToHtmlEvents: function () {
            var self = this;
            function _changed() {
                self.changed();
            }
            return this
                ._super()
                .on('bind', this, function () {
                    this.$field().on('blur', _changed);
                })
                .on('unbind', this, function () {
                    this.$field().off('blur', _changed);
                });
        },
        $field: function() {
            return this.$().find('td.value:first input');
        },
        disable: function() {
            this.$field().attr('disabled', true).addClass('disabled');
        },
        enable: function() {
            this.$field().removeAttr('disabled').removeClass('disabled').focus();
        },
        getValue: function() {
            return this.$field().val();
        },
        getText: function() {
            return this.getValue();
        },
        setValue: function(value) {
            if (this.$field().val() == value) {
                return;
            }
            this.$field()
                .val(value)
                .trigger('change')
                .trigger('blur');
        }
    });
});

Mana.define('Mana/Admin/Field/TextArea', ['jquery', 'Mana/Admin/Field/Text'], function($, Text) {
    return Text.extend('Mana/Admin/Field/TextArea', {
        $field: function() {
            return this.$().find('td.value:first textarea');
        }
    });
});

Mana.define('Mana/Admin/Field/Wysiwyg', ['jquery', 'prototype', 'Mana/Admin/Field/TextArea', 'singleton:Mana/Core/UrlTemplate',
    'singleton:Mana/Core/Config'],
function($, $p, TextArea, urlTemplate, config) {
    return TextArea.extend('Mana/Admin/Field/Wysiwyg', {
        _subscribeToHtmlEvents: function () {
            var self = this;
            function _open() {
                self.open();
            }
            return this
                ._super()
                .on('bind', this, function () {
                    this.$button().on('click', _open);
                })
                .on('unbind', this, function () {
                    this.$button().off('click', _open);
                });
        },
        $button: function() {
            return this.$().find('td.value:first button');
        },
        disable: function() {
            this._super();
            this.$button().attr('disabled', true).addClass('disabled');
        },
        enable: function() {
            this._super();
            this.$button().removeAttr('disabled').removeClass('disabled').focus();
        },
        getPopupUrl: function() {
            return urlTemplate.decodeAttribute(config.getData('url.wysiwyg'));
        },
        getElementId: function() {
            return this.$field()[0].id;
        },
        open: function() {
            new Ajax.Request(this.getPopupUrl().replace('__0__', this.getElementId()), {
                parameters: {
                },
                onSuccess: function(transport) {
                    try {
                        this.openDialogWindow(transport.responseText, this.getElementId());
                    } catch(e) {
                        alert(e.message);
                    }
                }.bind(this)
            });
        },
        openDialogWindow : function(content, elementId) {
            this.overlayShowEffectOptions = Windows.overlayShowEffectOptions;
            this.overlayHideEffectOptions = Windows.overlayHideEffectOptions;
            Windows.overlayShowEffectOptions = {duration:0};
            Windows.overlayHideEffectOptions = {duration:0};

            Dialog.confirm(content, {
                draggable:true,
                resizable:true,
                closable:true,
                className:"magento",
                windowClassName:"popup-window m-editor",
                title:'WYSIWYG Editor',
                width:620,
                height:555,
                zIndex:1000,
                recenterAuto:false,
                hideEffect:Element.hide,
                showEffect:Element.show,
                id:"catalog-wysiwyg-editor",
                buttonClass:"form-button",
                okLabel:"Submit",
                ok: this.okDialogWindow.bind(this),
                cancel: this.closeDialogWindow.bind(this),
                onClose: this.closeDialogWindow.bind(this),
                firedElementId: elementId
            });

            content.evalScripts.bind(content).defer();

            $p(elementId+'_editor').value = $p(elementId).value;
        },
        okDialogWindow : function(dialogWindow) {
            if (dialogWindow.options.firedElementId) {
                wysiwygObj = eval('wysiwyg'+dialogWindow.options.firedElementId+'_editor');
                wysiwygObj.turnOff();
                if (tinyMCE.get(wysiwygObj.id)) {
                    $p(dialogWindow.options.firedElementId).value = tinyMCE.get(wysiwygObj.id).getContent();
                } else {
                    if ($p(dialogWindow.options.firedElementId+'_editor')) {
                        $p(dialogWindow.options.firedElementId).value = $p(dialogWindow.options.firedElementId+'_editor').value;
                    }
                }
            }
            this.closeDialogWindow(dialogWindow);
        },
        closeDialogWindow : function(dialogWindow) {
            // remove form validation event after closing editor to prevent errors during save main form
            if (typeof varienGlobalEvents != undefined && editorFormValidationHandler) {
                varienGlobalEvents.removeEventHandler('formSubmit', editorFormValidationHandler);
            }

            //IE fix - blocked form fields after closing
            $p(dialogWindow.options.firedElementId).focus();

            //destroy the instance of editor
            wysiwygObj = eval('wysiwyg'+dialogWindow.options.firedElementId+'_editor');
            if (tinyMCE.get(wysiwygObj.id)) {
               tinyMCE.execCommand('mceRemoveControl', true, wysiwygObj.id);
            }

            dialogWindow.close();
            Windows.overlayShowEffectOptions = this.overlayShowEffectOptions;
            Windows.overlayHideEffectOptions = this.overlayHideEffectOptions;
        }
    });
});


Mana.define('Mana/Admin/Field/MultiSelect', ['jquery', 'Mana/Admin/Field/Select'], function($, Select) {
    return Select.extend('Mana/Admin/Field/MultiSelect', {
    });
});

Mana.define('Mana/Admin/Field/Date', ['jquery', 'Mana/Admin/Field/Text'], function($, Text) {
    return Text.extend('Mana/Admin/Field/Date', {
    });
});

Mana.define('Mana/Admin/Form', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/Admin/Form', {
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    this.on('post', this, this.post);
                })
                .on('unload', this, function () {
                    this.off('post', this, this.post);
                });
        },
        $form: function() {
            return this.$().find('form');
        },
        post: function (e) {
            if (e.target.getId() == 'container') {
                $.merge(e.result, this.$form().serializeArray());
            }
        }
    });
});
Mana.define('Mana/Admin/Expression', ['jquery', 'singleton:Mana/Core/Config'], function ($, config) {
    return Mana.Object.extend('Mana/Admin/Expression', {
        seoify: function(expr) {
            expr = expr.toLowerCase();
            $.each(config.getData('url.symbols'), function(index, pair) {
                expr = expr.replace(new RegExp(pair.symbol.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&"), 'g'), pair.substitute);
            });
            return expr;
        }
    });
});
Mana.define('Mana/Admin/Aggregate', ['jquery', 'singleton:Mana/Admin/Expression'],
function ($, expression, undefined) {
    return Mana.Object.extend('Mana/Admin/Aggregate', {
        expr: function(fields, expr, count) {
            var result = [];
            for (var i = 0; i < count; i++) {
                var field = expr.replace(/X/g, i);
                if (fields[field].getValue()) {
                    result.push(fields[field].getText());
                }
            }
            return result;
        },
        glue: function(expr, separator, lastSeparator) {
            var length = expr.length;
            if (lastSeparator === undefined || length < 2) {
                return expr.join(separator);
            }
            else {
                return expr.slice(0, length - 1).join(separator) + lastSeparator + expr[length - 1];
            }
        },
        seoify: function(expr) {
            $.each(expr, function(i) {
                expr[i] = expression.seoify(expr[i]);
            });
            return expr;
        }
    });
});

