/**
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // for better JS merging




Mana.define('Mana/Content/Tree', ['jquery', 'Mana/Core/Block'],
function ($, Block) {
    return Block.extend('Mana/Content/Tree', {
        setCustomContent: function(content) {
            this.setContent(content);
        }
    });
});

Mana.define('Mana/Content/Tree/Search', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Ajax', 'singleton:Mana/Core'],
function ($, Block, layout, ajax, core) {
    return Block.extend('Mana/Content/Tree/Search', {
        _init: function() {
            this._super();
        },
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
        $field: function(){
            return this.$().find('input');
        },
        $tree: function(){
            return layout.getBlock('tree');
        },
        changed: function() {
            var self = this;
            var params = {
                current_url: window.location.toString(),
                search: this.$field()[0].getValue()
            };
            ajax.post(this.$().attr('data-load-url'), params, function (response) {
                if (core.isString(response)) {
                    self.$tree().setCustomContent(response);
                } else {
                    ajax.update(response);
                }
            });
        }
    });
});

//# sourceMappingURL=content.js.map