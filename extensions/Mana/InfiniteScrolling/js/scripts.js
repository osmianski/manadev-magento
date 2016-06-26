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
Mana.define('Mana/InfiniteScrolling/Engine', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Ajax',
    'singleton:Mana/Core/UrlTemplate', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core/Config',
    'singleton:Mana/Core/Json'],
function ($, Block, ajax, urlTemplate, layout, config, json) {
    return Block.extend('Mana/InfiniteScrolling/Engine', {
        // region Construction/Destruction/Event Binding
        // ------------------------------------------------

        _init: function() {

            this._super();

            this.debugScrolling = false;
        },

        _subscribeToHtmlEvents: function () {
            var self = this;

            function _scroll() {
                self.onScroll();
            }

            return this
                ._super()
                .on('bind', this, function () {
                    self.$pager().hide();
                    self.page = 1;
                    self.limit = this.getVisibleItemCount();
                    self.$scrollingArea().on('scroll', _scroll);
                    this.showShowMoreButton();
                })
                .on('unbind', this, function () {
                    self.$scrollingArea().off('scroll', _scroll);
                });

        },

        // endregion

        // region Properties
        // ------------------------------------------------

        getContainerSelector: function() {
            return this.$().data('container');
        },

        getRowSelector: function() {
            return this.getModeHandler().getRowSelector();
        },
        getProductCount: function() {
            return this.$().data('product-count');
        },

        getPageSize: function() {
            return this.$().data('page-size');
        },
        getLoaderSelector: function() {
            return this.getModeHandler().getLoaderSelector();
        },
        getPagerSelector: function() {
            return this.$().data('pager');
        },
        getUrlKey: function() {
            return this.$().data('url-key');
        },
        getRouteSeparator: function() {
            return this.$().data('route-separator');
        },
        getPageSeparator: function() {
            return this.$().data('page-separator');
        },
        getLimitSeparator: function() {
            return this.$().data('limit-separator');
        },
        getMode: function() {
            return this.$().data('mode');
        },
        getModeHandler: function() {
            if (!this.modeHandlers) {
                this.modeHandlers = {};
                var handlers = json.decodeAttribute(this.$().data('mode-handlers'));
                var classNames = [];
                var self = this;

                $.each(handlers, function(mode, className) {
                    classNames.push(className);
                });

                Mana.requireOptional(classNames, function () {
                    var classes = arguments;
                    $.each(handlers, function(mode, className) {
                        var c = classes[classNames.indexOf(className)];
                        self.modeHandlers[mode] = new c(self, mode);
                    });
                });
            }
            return this.modeHandlers[this.getMode()];
        },
        getEffectDuration: function() {
            return this.$().data('effect-duration');
        },

        // endregion

        // region Selectors
        // ------------------------------------------------

        $container: function () {
            return $(this.getContainerSelector());
        },

        $pager: function () {
            return $(this.getPagerSelector());
        },

        $items: function() {
            return this.getModeHandler().$items();
        },

        $rows: function() {
            return this.getModeHandler().$rows();
        },

        $loaderTemplate: function() {
            return this.$().find(this.getLoaderSelector());
        },

        $loader: function () {
            return this.getModeHandler().$loader();
        },

        $loaderLocation: function () {
            return this.getModeHandler().$loaderLocation();
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
            var $lastItem = this.$rows().last();

            return $lastItem.offset().top + $lastItem.height();
        },

        getVisibleItemCount: function() {
            return this.$items().length;
        },

        getPagesPerShowMore: function () {
            return this.$().data('pages-per-show-more');
        },

        getRecoverScrollProgressOnBack: function() {
            return this.$().data('recover-scroll-progress-on-back');
        },

        getShowMoreText: function () {
            return this.$().data('show-more-caption');
        },

        isShowMoreButtonVisible: function() {
            return $('#m-show-more').length != 0;
        },
        getItemSelector: function() {
            return this.getContainerSelector() + " " + this.getModeHandler().getItemSelector();
        },

        showShowMoreButton: function () {
            var self = this;
            if (self.getVisibleItemCount() < self.getProductCount() && !this.isShowMoreButtonVisible() && self.page % self.getPagesPerShowMore() == 0) {
                var button = $("<button id='m-show-more'><span>"+ self.getShowMoreText() +"</span></button>");
                button.insertAfter($(this.getItemSelector()).last().parent());
                button.addClass('button');
                button.on('click', function () {
                    $(this).remove();
                    self.load(self.page + 1, self.limit);
                });
            }
        },
        // endregion

        // region Product Loading
        // ------------------------------------------------

        load: function(page, limit, callback, reset) {
            var self = this;
            var reset = (reset) ? reset : false;
            if(reset) {
                self.page = 0;
                limit = page * limit;
                if(limit == 0) {
                    callback();
                    return;
                }
            }
            self.showLoader();

            var url = ajax.getDocumentUrl();

            // decode %2B style encoded chars into UTF8
            var encodedUrl = url;
            url = decodeURIComponent(url);
            var encodedQueryPos = encodedUrl.indexOf('?'), queryPos = url.indexOf('?');
            if (encodedQueryPos != -1 && queryPos != -1) {
                url = url.substr(0, queryPos) + '?' + encodedUrl.substr(encodedQueryPos + 1);
            }

            url = config.getBaseUrl(url) + this.getUrlKey() +
                '/' + config.getData('ajax.currentRoute') +
                '/' + this.getPageSeparator() +
                '/' + (self.page+1) +
                '/' + this.getLimitSeparator() +
                '/' + limit +
                '/' + this.getRouteSeparator() +
                '/' + url.substr(config.getBaseUrl(url).length);

            ajax.get(url, function (response) {
                self.addContent(response, reset);
                if(reset) {
                    self.page = parseInt(page);
                } else {
                    self.page++;
                }

                $(response).filter("script").each(function (e) {
                    $.globalEval(this.innerHTML);
                });

                if (typeof ProductMediaManager != 'undefined') {
                    $(document).trigger('product-media-loaded', ProductMediaManager);
                }

                self.hideLoader();
                layout.getPageBlock().resize();
                if(self.page == page) {
                    self.showShowMoreButton();
                    callback();
                } else {
                    window.scrollTo(null, self.$rows().last().offset().top - 20);
                    self.load(page, limit, callback);
                }
            }, { showWait: false, showOverlay: false, encode: queryPos != -1 ? { offset: 0, length: queryPos} : undefined });

        },

        showLoader: function() {
            this.loaderVisible = true;
            this.$loaderLocation().after(this.$loaderTemplate().clone());
        },

        hideLoader: function() {
            this.loaderVisible = false;
            this.$loader().remove();
        },

        isLoaderVisible: function() {
            return this.loaderVisible;
        },

        addContent: function(content, reset) {
            var $content = $(content);
            var $newRows = $content.find(this.getRowSelector());
            var self = this;

            // prepare effect
            $newRows.hide();

            var parent = $(self.$rows().parent());
            if(reset) {
                parent.html("");
            }

            // insert new data
            self.$rows().last().removeClass('last');
            $newRows.each(function() {
                try{
                    parent.append(this);
                } catch(e) {
                    console.log("The following error occurred while trying to load next page via infinite scrolling:");
                    // Display appended HTML javascript error.
                    console.error(e);
                    console.info(this);
                }
            });

            // start effect
            $newRows.fadeIn(this.getEffectDuration());
        },

        // endregion

        // region Event Handlers
        // ------------------------------------------------

        onScroll: function () {
            if (this.debugScrolling) {
                console.log('visible bottom: %d, list bottom: %d, visible count: %d, product count: %d',
                    this.getScrollingAreaBottom(), this.getProductListBottom(),
                    this.getVisibleItemCount(), this.getProductCount());
            }

            // when window bottom reaches product list bottom
            if (this.getVisibleItemCount() < this.getProductCount() &&
                this.getScrollingAreaBottom() >= this.getProductListBottom() &&
                !this.isLoaderVisible() && !this.isShowMoreButtonVisible())
            {
                this.load(this.page + 1, this.limit);
            }
        }

        // endregion

    });
});


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


Mana.require(['jquery', 'singleton:Mana/Core/Layout'], function ($, layout) {
    $(function () {
        var Engine = layout.getBlock('infinitescrolling-engine');
        if(!Engine) {
            return;
        }

        function isElementInViewport(el) {

            //special bonus for those using jQuery
            if (typeof jQuery === "function" && el instanceof jQuery) {
                el = el[0];
            }

            var rect = el.getBoundingClientRect();

            // If 70% of height is visible, consider it in viewport.
            return (rect.bottom / el.getHeight()) * 100 >= 70;
        }

        var selector = Engine.getItemSelector();
        if(Engine.getRecoverScrollProgressOnBack()) {
            $(document).on('click', selector, function(e) {
                var productImageList = $(selector);
                var index = 0;
                productImageList.each(function(i) {
                    if(isElementInViewport(this)) {
                        index = i;
                        return false;
                    }
                });
                location.hash = "index=" + index + "&page=" + Engine.page;
            });

            var currentUrl = location.href;


            var hash = currentUrl.split("#")[1];
            if (hash) {
                var rawDataArr = hash.split("&");
                var data = {};
                rawDataArr.each(function (rawData) {
                    var key = rawData.split("=")[0],
                        value = rawData.split("=")[1];

                    data[key] = value;
                });

                var showMoreButton = $("#m-show-more");
                if (showMoreButton) {
                    showMoreButton.remove();
                }

                window.scrollTo(null, Engine.getProductListBottom());
                Engine.load(data.page, Engine.limit, function () {
                        var m_timer = setInterval(function() {
                            var m_allImagesLoaded = true;
                            $(selector + ' img').each(function() {
                                if (!$(this).height()) {
                                    m_allImagesLoaded = false;
                                }
                            });

                            function getHeightOfFixedElements() {
                                // Default allowance
                                var result = 10;
                                var elements = document.getElementsByTagName('*');
                                for (var i in elements) {
                                    try {
                                        var position = jQuery.css(elements[i], 'position');
                                        if (position == "fixed") {
                                            var height = jQuery.css(elements[i], 'height');
                                            var display = jQuery.css(elements[i], 'display');
                                            var top = jQuery.css(elements[i], 'top');
                                            if(height != "0px" && display != "none" && top == "0px") {
                                                height = height.replace("px", "");
                                                height = parseInt(height);
                                                result = height;
                                                break;
                                            }
                                        }
                                    } catch (err) {
                                    }
                                }
                                return result;
                            }

                            if (m_allImagesLoaded) {
                                clearInterval(m_timer);

                                // DO YOUR STUFF HERE
                                var space = getHeightOfFixedElements();
                                var topPosition = $(selector).eq(data.index).offset().top - space;
                                window.scrollTo(null, topPosition);
                            }

                        }, 100); // timeout to load the images
                }, true);
            }
        }
    });
});


//# sourceMappingURL=infinitescrolling.js.map