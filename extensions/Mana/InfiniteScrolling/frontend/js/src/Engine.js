/**
 * Infinite Scrolling engine is client-side block. It contains loader template which is shown during loading
 * additional content.
 */
Mana.define('Mana/InfiniteScrolling/Engine',
['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Ajax', 'singleton:Mana/Core/UrlTemplate'],
function ($, Block, ajax, urlTemplate) {
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
        getLoaderSelector: function() {
            return this.$().data('loader');
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

        $loaderTemplate: function() {
            return this.$().find(this.getLoaderSelector());
        },

        $listLoader: function () {
            return this.$list().find(this.getLoaderSelector());
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

        // region Product Loading
        // ------------------------------------------------

        load: function(offset, count) {
            var self = this;
            self.showLoader();
            ajax.get(url, function (response) {
                ajax.update(response);
                layout.getPageBlock().resize();

                if ($('#nav') && typeof mainNav != 'undefined') {
                    mainNav("nav", {"show_delay": "100", "hide_delay": "100"});
                }

                if (isProductListToolbarClicked && config.getData('layeredNavigation.ajax.scrollToTop')) {
                    var offset = -1;
                    $.each(response.blocks, function (blockName) {
                        var block = layout.getBlock(blockName);
                        if (block) {
                            var blockOffset = block.$().offset().top;
                            if (offset == -1 || offset >= blockOffset) {
                                offset = blockOffset;
                            }
                        }
                    });
                    if (offset >= 0) {
                        offset -= 10;
                        if (offset < 0) {
                            offset = 0;
                        }
                        //noinspection JSUnresolvedFunction
                        scroll(0, offset);
                    }
                }
            }, { preventClicks: true, encode: queryPos != -1 ? { offset: 0, length : queryPos} : undefined });
        },

        showLoader: function() {
            this.$list().append(this.$loaderTemplate());
        },

        hideLoader: function() {
            this.$listLoader().remove();
        },

        isLoaderVisible: function() {
            return this.$listLoader().length > 0;
        },

        // endregion

        // region Event Handlers
        // ------------------------------------------------

        onScroll: function () {
            // when window bottom reaches product list bottom
            if (this.getScrollingAreaBottom() >= this.getProductListBottom() &&
                this.getVisibleItemCount() < this.getProductCount())
            {
                this.load(this.getVisibleItemCount(), Math.max(this.getPageSize(),
                    this.getProductCount() - this.getVisibleItemCount()));
            }
        }

        // endregion

    });
});
