/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('Mana/Admin/Grid', ['jquery', 'Mana/Core/Block', 'Mana/Admin/Grid/Row',
    'Mana/Admin/Grid/Column', 'singleton:Mana/Core',
    'singleton:Mana/Core/Ajax', 'singleton:Mana/Core/Json', 'singleton:Mana/Core/Config',
    'singleton:Mana/Core/Base64', 'singleton:Mana/Core/UrlTemplate'],
function ($, Block, Row, Column, core, ajax, json, config, base64, urlTemplate)
{
    var RewrittenVarienGrid = Class.create(varienGrid, {
        reload:function (url) {
            if (!this.reloadParams) {
                this.reloadParams = {form_key:FORM_KEY};
            }
            else {
                this.reloadParams.form_key = FORM_KEY;
            }
            url = url || this.url;

            this._block._updateReloadParams();

            ajax.post(url, this.reloadParams || {}, ajax.response(this._block));
        }
    });

    return Block.extend('Mana/Admin/Grid', {
        _init: function() {
            this._super();
            this._varienGrid = null;
            this._url = '';
            this._edit = {
                pending:{},
                saved:{},
                deleted:{}
            };
        },

        getUrl: function() {
            if (!this._url) {
                this._url = urlTemplate.decodeAttribute($(this.getElement()).data('url'));
            }
            return this._url;
        },
        setUrl: function(value) {
            this._url = value;
            return this;
        },
        getEdit: function() {
            return this._edit;
        },
        setEdit: function(value) {
            this._edit = value;
            return this;
        },

        //region Event handlers
        _subscribeToHtmlEvents:function () {
            return this
                ._super()
                .on('bind', this, function () {
                    //noinspection JSPotentiallyInvalidConstructorUsage
                    this._varienGrid = new RewrittenVarienGrid(this.getElement().id,
                        this.getUrl().replace('{action}', 'index'), 'page', 'sort', 'dir', 'filter');
                    this._varienGrid.useAjax = true;
                    this._varienGrid._block = this;

                    var edit = $(this.getElement()).data('edit');
                    if (edit) {
                        this._edit = json.decodeAttribute(edit);
                    }
                })
                .on('unbind', this, function () {
                    this._varienGrid = null;
                });
        },
        _subscribeToBlockEvents:function() {
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
        search: function() {
            this._varienGrid.doFilter();
            return this;
        },
        reset: function() {
            this._varienGrid.resetFilter();
            return this;
        },
        addRow: function() {
            this._call('add');
            return this;
        },
        removeRow: function() {
            this._call('remove');
            return this;
        },
        post: function(e) {
            if (e.target.getId() == 'page') {
                e.result[this.getAlias()] = json.stringify(this.getEdit());
            }
        },
        //endregion
        _call:function(action, args) {
            var gridUrl = this._varienGrid.url;

            this._varienGrid.url = this.getUrl().replace('{action}', action);
            if (args) {
                this._varienGrid.addVarToUrl('args', base64.encode(json.stringify(args)));
            }

            var url = this._varienGrid.url;
            this._varienGrid.url = gridUrl;

            this._varienGrid.reload(url);
        },
        _updateReloadParams: function() {
            var id = this.getElement().id;
            if (!this._varienGrid.reloadParams) {
                this._varienGrid.reloadParams = {};
            }
            this._varienGrid.reloadParams['edit'] = json.stringify($.extend(
                { sessionId: config.getData('editSessionId') },
                this.getEdit()
            ));
        },
        getRows: function() {
            return this.getChildren(function (index, child) {
                return child instanceof Row;
            });
        },
        getColumns:function () {
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
        setCellValue: function(cell, compositeValue) {
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
Mana.define('Mana/Admin/Action', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/Admin/Action', {
        _init: function() {
            this._super();
            this.setIsSelfContained(true);
        },
        //region Event handlers
        _subscribeToHtmlEvents:function () {
            var self = this;
            var _raiseClick = function () { self.trigger('click'); };
            return this
                ._super()
                .on('bind', this, function () {
                    $(this.getElement()).on('click', _raiseClick);
                })
                .on('unbind', this, function () {
                    $(this.getElement()).off('click', _raiseClick);
                });
        }
        //endregion
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
Mana.define('Mana/Admin/Grid/Row', ['jquery', 'Mana/Core/Block', 'Mana/Admin/Grid/Cell'],
function ($, Block, Cell)
{
    return Block.extend('Mana/Admin/Grid/Row', {
        _init:function () {
            this._super();
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
        }
    });
});
Mana.define('Mana/Admin/Grid/Cell', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/Admin/Grid/Cell', {
        _init:function () {
            this._super();
            this.setIsSelfContained(true);
        },
        getRow: function() {
            return this.getParent();
        },
        getGrid: function() {
            return this.getRow().getGrid();
        },
        getColumn: function() {
            return this.getGrid().getColumn($.inArray(this, this.getRow().getChildren()));
        }
    });
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
        }
    });
});
Mana.define('Mana/Admin/Grid/Cell/Massaction', ['jquery', 'Mana/Admin/Grid/Cell/Checkbox'], function ($, Checkbox) {
    return Checkbox.extend('Mana/Admin/Grid/Cell/Massaction', {
    });
});
Mana.define('Mana/Admin/Tab', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/Admin/Tab', {
    });
});
Mana.define('Mana/Admin/Page', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/UrlTemplate',
    'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Ajax', 'singleton:Mana/Core/Config', 'singleton:Mana/Core'],
function ($, Block, urlTemplate, layout, ajax, config, core)
{
    return Block.extend('Mana/Admin/Page', {
        getUrl:function () {
            if (!this._url) {
                this._url = urlTemplate.decodeAttribute($(this.getElement()).data('url'));
            }
            return this._url;
        },
        setUrl:function (value) {
            this._url = value;
            return this;
        },

        _subscribeToBlockEvents:function () {
            return this
                ._super()
                .on('load', this, function () {
                    if (this.getChild('close')) this.getChild('close').on('click', this, this.close);
                    if (this.getChild('save')) this.getChild('save').on('click', this, this.save);
                    if (this.getChild('saveAndClose')) this.getChild('saveAndClose').on('click', this, this.saveAndClose);
                })
                .on('unload', this, function () {
                    if (this.getChild('close')) this.getChild('close').off('click', this, this.close);
                    if (this.getChild('save')) this.getChild('save').off('click', this, this.save);
                    if (this.getChild('saveAndClose')) this.getChild('saveAndClose').off('click', this, this.saveAndClose);
                });
        },
        save: function() {
            var params = {
                form_key:FORM_KEY,
                sessionId:config.getData('editSessionId')
            };
            params = layout.getPageBlock().trigger('post', { target: this, result: params}, false, true);
            var self = this;

            ajax.post(this.getUrl().replace('{action}', 'save'), params, ajax.response(this));
        },
        close: function() {
            window.location = this.getUrl().replace('{action}', 'index');
        },
        saveAndClose: function() {
            var self = this;
            this.save(function() {
                self.close();
            });
        }

    });
});
