Mana.define('Mana/Content/Tree/Search', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Config'],
function ($, Block, layout, config) {
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
            var url = config.getData('url.unfiltered');

            url += "?search="+ this.$field()[0].getValue();
            setLocation(url);
        }
    });
});