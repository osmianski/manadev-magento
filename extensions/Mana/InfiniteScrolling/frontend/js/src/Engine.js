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

        // endregion

        // region Selectors
        // ------------------------------------------------

        $list: function () {
            return $(this.getListSelector());
        },

        $items: function() {
            return this.$list().find(this.getListItemSelector());
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

        },

        // endregion

        // region Event Handlers
        // ------------------------------------------------

        onScroll: function () {

        }

        // endregion

    });
});
