/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('Mana/Admin/Block/Grid', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core',
    'singleton:Mana/Core/Ajax', 'singleton:Mana/Core/Json', 'singleton:Mana/Core/Config',
    'singleton:Mana/Core/Base64', 'singleton:Mana/Core/UrlTemplate'],
function ($, Block, core, ajax, json, config, base64, urlTemplate)
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

            var self = this;
            ajax.post(url, this.reloadParams || {}, function(response) {
                self._block.setContent(response);
            });
        }
    });

    return Block.extend('Mana/Admin/Block/Grid', {
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
                })
                .on('unload', this, function () {
                    if (this.getChild('search')) this.getChild('search').off('click', this, this.search);
                    if (this.getChild('reset')) this.getChild('reset').off('click', this, this.reset);
                    if (this.getChild('add')) this.getChild('add').off('click', this, this.addRow);
                    if (this.getChild('remove')) this.getChild('remove').off('click', this, this.removeRow);
                });
        },
        search: function() {
            this._updateReloadParams();
            this._varienGrid.doFilter();
            return this;
        },
        reset: function() {
            this._updateReloadParams();
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
        //endregion
        _call:function(action, args) {
            var gridUrl = this._varienGrid.url;

            this._varienGrid.url = this.getUrl().replace('{action}', action);
            if (args) {
                this._varienGrid.addVarToUrl('args', base64.encode(json.stringify(args)));
            }

            var url = this._varienGrid.url;
            this._varienGrid.url = gridUrl;

            this._updateReloadParams();
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

            if ($.options('edit-form')) {
                //noinspection JSJQueryEfficiency
                if (!$('#' + id + 'SerializedData').length) {
                    $('#' + id).append('<input type="hidden" name="' + id + '" id="' + id + 'SerializedData" />');
                }
                //noinspection JSJQueryEfficiency
                $('#' + id + 'SerializedData').val(this._varienGrid.reloadParams['edit']);
            }
        }
    });
});
Mana.define('Mana/Admin/Block/Action', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/Admin/Block/Action', {
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
Mana.define('Mana/Admin/Block/Grid/Column', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/Admin/Block/Grid/Column', {
        _init:function () {
            this._super();
            this.setIsSelfContained(true);
        }
    });
});
Mana.define('Mana/Admin/Block/Grid/Row', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/Admin/Block/Grid/Row', {
        _init:function () {
            this._super();
            this.setIsSelfContained(true);
        },
        getGrid: function() {
            return this.getParent();
        }
    });
});
Mana.define('Mana/Admin/Block/Grid/Cell', ['jquery', 'Mana/Core/Block', 'Mana/Admin/Block/Grid/Column'],
function ($, Block, Column)
{
    return Block.extend('Mana/Admin/Block/Grid/Cell', {
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
            var cellIndex = $.inArray(this, this.getRow().getChildren());
            var columnIndex = -1;
            $.each(this.getGrid().getChildren(), function(index, column) {
                if (column instanceof Column) {
                    columnIndex++;
                }
            });
        }
    });
});

Mana.define('Mana/Admin/Block/Grid/Cell/Select', ['jquery', 'Mana/Admin/Block/Grid/Cell'], function ($, Cell) {
    return Cell.extend('Mana/Admin/Block/Grid/Cell/Select', {
    });
});
Mana.define('Mana/Admin/Block/Grid/Cell/Input', ['jquery', 'Mana/Admin/Block/Grid/Cell'], function ($, Cell) {
    return Cell.extend('Mana/Admin/Block/Grid/Cell/Select', {
    });
});
