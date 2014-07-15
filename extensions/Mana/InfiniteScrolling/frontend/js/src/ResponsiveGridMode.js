/**
 * This class handle adding of received content to product list in `list` mode
 */
Mana.define('Mana/InfiniteScrolling/ResponsiveGridMode', ['jquery', 'Mana/InfiniteScrolling/ListMode'],
function ($, ListMode) {
    return ListMode.extend('Mana/InfiniteScrolling/ResponsiveGridMode', {
        _init: function(engine, mode) {
            this._super(engine, 'list');
        }
    });
});
