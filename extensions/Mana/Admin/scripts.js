/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

Mana.define('Mana/Admin/Block/Grid', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core', 'singleton:Mana/Core/Ajax'],
function ($, Block, core, ajax)
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
            this._gridUrl = '';
        },

        getGridUrl: function() {
            if (!this._gridUrl) {
                this._gridUrl = core.base64Decode($(this.getElement()).data('grid-url'));
            }
            return this._gridUrl;
        },
        setGridUrl: function(value) {
            this._gridUrl = value;
            return this;
        },

        //region Event handlers
        _subscribeToHtmlEvents:function () {
            return this
                ._super()
                .on('bind', this, function () {
                    //noinspection JSPotentiallyInvalidConstructorUsage
                    this._varienGrid = new RewrittenVarienGrid(this.getElement().id,
                        this.getGridUrl().replace('{action}', 'index'), 'page', 'sort', 'dir', 'filter');
                    this._varienGrid.useAjax = true;
                    this._varienGrid._block = this;

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
        //endregion
        _call:function(action, args) {
            var gridUrl = this._varienGrid.url;

            this._varienGrid.url = this.getGridUrl().replace('{action}', action);
            if (args) {
                this._varienGrid.addVarToUrl('args', encode_base64(Object.toJSON(args)));
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
            if ($.options('edit-form')) {
                this._varienGrid.reloadParams['edit'] = {};
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
            var _raiseClick = function () {
                self.trigger('click');
            };
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
        }
    });
});

Mana.define('Mana/Admin/Block/Grid/Cell', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/Admin/Block/Grid/Cell', {
        _init:function () {
            this._super();
            this.setIsSelfContained(true);
        }
    });
});
