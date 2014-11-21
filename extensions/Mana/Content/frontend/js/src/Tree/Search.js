Mana.define('Mana/Content/Tree/Search', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Config', 'singleton:Mana/Content/Filter'],
function ($, Block, layout, config, filter) {
    return Block.extend('Mana/Content/Tree/Search', {
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
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function(){
                    this.$field()[0].setValue(filter._searchValue);
                })
        },
        $field: function(){
            return this.$().find('input');
        },
        $link: function() {
            return this.$().find('a');
        },
        $tree: function(){
            return layout.getBlock('tree');
        },
        changed: function() {
            this.$link()[0].href = filter.setSearch(this.$field()[0].getValue());
        }
    });
});