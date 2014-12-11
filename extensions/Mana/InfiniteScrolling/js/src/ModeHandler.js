/**
 * This class handle adding of received content to product list in `list` mode
 */
Mana.define('Mana/InfiniteScrolling/ModeHandler', ['jquery'],
function ($) {
    return Mana.Object.extend('Mana/InfiniteScrolling/ModeHandler', {
        _init: function(engine, mode) {
            this.engine = engine;
            this.mode = mode;
        },

        getItemSelector: function() {
            return this.engine.$().data('item-in-' + this.mode + '-mode');
        },

        getRowSelector: function() {
            return this.engine.$().data('row-in-' + this.mode + '-mode');
        },

        getLoaderSelector: function() {
            return this.engine.$().data('loader-in-' + this.mode + '-mode');
        },

        $items: function() {
            return this.engine.$container().find(this.getItemSelector());
        },

        $rows: function() {
            return this.engine.$container().find(this.getRowSelector());
        },

        $loader: function () {
            return this.engine.$container().find(this.getLoaderSelector());
        },
        $loaderLocation: function() {
            return this.$rows().last();
        }
    });
});
