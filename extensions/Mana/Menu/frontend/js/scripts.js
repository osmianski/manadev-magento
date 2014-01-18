/**
 * @category    Mana
 * @package     Mana_Menu
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // for better JS merging

Mana.define('Mana/Menu/Tree', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/UrlTemplate', 'singleton:Mana/Core/Json'],
function($, Block, urlTemplate, json, undefined)
{
    return Block.extend('Mana/Menu/Tree', {
        _init: function() {
            this._super();
            this._state = {};
        },
        _subscribeToHtmlEvents: function () {
            var self = this;

            function _click(e) {
                return self.click(this, e);
            }

            return this
                ._super()
                .on('bind', this, function () {
                    this._loadState();
                    this.$().find('.m-item').on('click', _click);
                })
                .on('unbind', this, function () {
                    this.$().find('.m-item').off('click', _click);
                });
        },
        _collapse: function (item, showEffect) {
            var li = item.parent();
            if (!li.hasClass('m-animating')) {
                if (showEffect) {
                    this._saveState(item, true);
                    li.addClass('m-animating');
                    li.children('ul').slideUp('fast', function () {
                        li.removeClass('m-animating').removeClass('m-expanded').addClass('m-collapsed');
                    });
                }
                else {
                    li.children('ul').hide();
                    li.removeClass('m-expanded').addClass('m-collapsed');
                }
            }
        },
        _expand: function (item, showEffect) {
            var li = item.parent();
            if (!li.hasClass('m-animating')) {
                if (showEffect) {
                    this._saveState(item, false);
                    li.addClass('m-animating');
                    li.children('ul').slideDown('fast', function () {
                        li.removeClass('m-animating').removeClass('m-collapsed').addClass('m-expanded');
                    });
                }
                else {
                    li.children('ul').show();
                    li.removeClass('m-collapsed').addClass('m-expanded');
                }
            }
        },
        _saveState: function (item, liState) {
            var itemId = item.data('id');
            var isCollapsed = liState;
            if (this.getCollapsedByDefault()) {
                isCollapsed = !isCollapsed;
            }

            this._state[itemId] = isCollapsed ? 1 : 0;
            if (this.getUrl()) {
                $.post(this.getUrl(), {id: this.getId(), state: this._state});
            }
        },
        _loadState: function _loadState() {
            var self = this;
            var state = this._state;
            var previousState = this.$().data('state');
            if (!state.length && previousState) {
                state = json.decodeAttribute(previousState);
            }

            this.$().find('.m-item').each(function () {
                var item = $(this);
                var li = item.parent();
                if (li.children('ul').length) {
                    var itemId = item.data('id');
                    var isCollapsed;

                    if (li.find('ul .m-selected').length) {
                        isCollapsed = false;
                    }
                    else if (li.hasClass('.m-selected').length && self.getExpandSelected()) {
                        isCollapsed = false;
                    }
                    else {
                        isCollapsed = state[itemId] == 1;
                        if (self.getCollapsedByDefault()) {
                            isCollapsed = !isCollapsed;
                        }
                    }

                    if (isCollapsed) {
                        self._collapse($(this), false);
                    }
                    else {
                        self._expand($(this), false);
                    }
                }
                else {
                    li.addClass('m-leaf');
                }
            });
        },
        getCollapsedByDefault: function() {
            if (this._collapsedByDefault === undefined) {
                this._collapsedByDefault = !this.$().data('expand-by-default');
            }
            return this._collapsedByDefault;
        },
        getExpandSelected: function() {
            if (this._expandSelected === undefined) {
                this._expandSelected = !this.$().data('collapse-selected');
            }
            return this._expandSelected;
        },
        getUrl: function() {
            if (this._url === undefined) {
                var data = this.$().data('url');
                this._url = data ? urlTemplate.decodeAttribute(data) : false;
            }
            return this._url;
        },
        click: function(item, e) {
            item = $(item);
            if ($(e.target).prop("tagName").toLowerCase() == 'a') {
                return true;
            }
            if (item.parent().hasClass('m-collapsed')) {
                this._expand(item, true);
                return false;
            }
            else if (item.parent().hasClass('m-expanded')) {
                this._collapse(item, true);
                return false;
            }
            return true;
        }

    });
});
