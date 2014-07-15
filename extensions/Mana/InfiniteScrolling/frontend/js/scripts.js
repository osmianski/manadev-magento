/**
 * @category    Mana
 * @package     Mana_InfiniteScrolling
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // for better JS merging



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


/**
 * This class handle adding of received content to product list in `list` mode
 */
Mana.define('Mana/InfiniteScrolling/GridMode', ['jquery', 'Mana/InfiniteScrolling/ModeHandler'],
function ($, ModeHandler) {
    return ModeHandler.extend('Mana/InfiniteScrolling/GridMode', {
    });
});


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


//# sourceMappingURL=infinitescrolling.js.map