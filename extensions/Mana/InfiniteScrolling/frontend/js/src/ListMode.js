/**
 * This class handle adding of received content to product list in `list` mode
 */
Mana.define('Mana/InfiniteScrolling/ListMode', ['jquery', 'Mana/InfiniteScrolling/ModeHandler'],
function ($, ModeHandler) {
    return ModeHandler.extend('Mana/InfiniteScrolling/ListMode', {
        $loaderLocation: function() {
            return this.$rows().last().parent();
        }
    });
});
