Mana.define('Mana/Content/Book/Tree', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Json'],
function ($, Block, json)
{
    return Block.extend('Mana/Content/Book/Tree', {
        _subscribeToHtmlEvents: function() {
            return this
                ._super()
                .on('bind', this, function () {
                    this.$().jstree(this.getOptions());
                })
                .on('unbind', this, function () {
                });
        },
        getOptions: function() {
            var options = {
            };
            var dynamicOptions = this.$().data('options');
            if (dynamicOptions) {
                $.extend(true, options, json.decodeAttribute(dynamicOptions));
            }

            return options;
        }
    });
});
