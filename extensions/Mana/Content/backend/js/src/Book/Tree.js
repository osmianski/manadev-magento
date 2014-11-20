Mana.define('Mana/Content/Book/Tree', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Json', 'singleton:Mana/Core/Layout'],
function ($, Block, json, layout)
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
                    var container = this.$container();
                    container.startingId = options.core.data.id;
                    var self = this;

                    options.core.check_callback = function (op, node, par, pos, more) {
                        if(more && more.dnd && (op === 'move_node' || op === 'copy_node')) {
                            if(
                                // Parent should not be moved/copied
                                par.id == "#" ||
                                // Only nodes without children (leaf nodes) are able to make a copy and reference
                                (((op === 'move_node' && container.triggerReference) || op === 'copy_node') && node.children.length > 0) ||
                                // Do not allow reference pages and referenced pages(original page that has reference) to have children
                                (op === 'move_node' || op === 'copy_node') && self.isTargetReferencePage(par)
                                ) {
                                return false;
                            }
                          }
                          return true;
                    };
                    this.$().jstree(options);
                })
                .on('unbind', this, function () {
                });
        },
        isTargetReferencePage: function (target) {
            var reference_pages = this.$container().reference_pages;
            for(var i in reference_pages) {
                if(reference_pages[i].id == target.id || reference_pages[i].reference_id == target.id) {
                    return true;
                }
            }
            return false;
        },
        $container: function() {
            return layout.getBlock('container');
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