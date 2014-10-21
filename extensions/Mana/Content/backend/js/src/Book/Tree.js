Mana.define('Mana/Content/Book/Tree', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Json'],
function ($, Block, json)
{
    return Block.extend('Mana/Content/Book/Tree', {
        _subscribeToHtmlEvents: function() {
            return this
                ._super()
                .on('bind', this, function () {
                    var options = this.getOptions();
                    if(options.core.data.id == null) {
                        options.core.data.id = "n" + this.createGuid();
                    }

                    options.core.check_callback = function (op, node, par, pos, more) {
                        if(more && more.dnd && (op === 'move_node') && par.id == "#") {
                            return false;
                          }
                          return true;
                    };
                    this.$().jstree(options);
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
        },
        createGuid: function () {
            function s4() {
                return Math.floor(Math.random(0, 9) * 10).toString();
            }

            return s4() + s4() + s4() + s4() +
                s4() + s4() + s4() + s4();
        }
    });
});