/**
 * @category    Mana
 * @package     Mana_InfiniteScrolling
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // for better JS merging



/**
 * Infinite Scrolling engine is client-side block. It contains loader template which is shown during loading
 * additional content.
 */
Mana.define('Mana/InfiniteScrolling/Engine', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/InfiniteScrolling/Engine', {
        // region Construction/Destruction/Event Binding
        // ------------------------------------------------

        _subscribeToHtmlEvents: function () {
            var self = this;

            function _scroll() {
                self.onScroll();
            }

            return this
                ._super()
                .on('bind', this, function () {
                    self.$scrollingArea().on('scroll', _scroll);
                })
                .on('unbind', this, function () {
                    self.$scrollingArea().off('scroll', _scroll);
                });

        },

        // endregion

        // region Properties
        // ------------------------------------------------

        getListSelector: function() {
            return this.$().data('list');
        },

        getListItemSelector: function() {
            return this.$().data('list-item');
        },

        getProductCount: function() {
            return this.$().data('product-count');
        },

        getPageSize: function() {
            return this.$().data('page-size');
        },

        // endregion

        // region Selectors
        // ------------------------------------------------

        $list: function () {
            return $(this.getListSelector());
        },

        $items: function() {
            return this.$list().find(this.getListItemSelector());
        },

        $loader: function() {
            return this.$().find('.infinite-scrolling-loader');
        },

        $scrollingArea: function() {
            return $(window);
        },

        // endregion

        // region Helpers
        // ------------------------------------------------

        getScrollingAreaBottom: function () {
            return $(window).scrollTop() + $(window).height();
        },

        getProductListBottom: function() {
            var $lastItem = this.$items().last();

            return $lastItem.offset().top + $lastItem.height();
        },

        getVisibleItemCount: function() {
            return this.$items().length;
        },

        // endregion

        // region Event Handlers
        // ------------------------------------------------

        onScroll: function () {
            // when window bottom reaches product list bottom
            if (this.getScrollingAreaBottom() >= this.getProductListBottom() &&
                this.getVisibleItemCount() < this.getProductCount())
            {
                this.load(this.getVisibleItemCount(), );
            }
        }

        // endregion

    });
});


//# sourceMappingURL=mana_infinitescrolling.js.map