Mana.define('ManaPro/Content/Tree/Search', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Config', 'singleton:ManaPro/Content/Filter'],
function ($, Block, layout, config, filter) {
    return Block.extend('ManaPro/Content/Tree/Search', {
        _subscribeToHtmlEvents: function () {
            var self = this;

            function _submit(e) {
                self.changed();
                self.submit();
                e.preventDefault();
            }

            return this
                ._super()
                .on('bind', this, function () {
                    this.$form().on('submit', _submit);
                })
                .on('unbind', this, function () {
                    this.$form().off('submit', _submit);
                });
        },
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function(){
                    this.$field()[0].setValue(filter._searchValue);
                })
        },
        submit: function() {
            this.$link()[0].href = this.$form()[0].action;
            this.$link().click();
        },
        $field: function(){
            return this.$().find('input');
        },
        $form: function () {
            return this.$().find('form');
        },
        $link: function() {
            return this.$().find('a');
        },
        $tree: function(){
            return layout.getBlock('tree');
        },
        changed: function() {
            this.$form()[0].action = filter.setSearch(this.$field()[0].getValue());
        }
    });
});