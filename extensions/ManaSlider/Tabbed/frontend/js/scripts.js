/**
 * @category    Mana
 * @package     ManaSlider_Tabbed
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('ManaSlider/Tabbed/ProductSlider', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Json',
    'singleton:Mana/Core/Ajax', 'singleton:Mana/Core/UrlTemplate', 'singleton:Mana/Core'],
function ($, Block, json, ajax, urlTemplate, core)
{
    return Block.extend('ManaSlider/Tabbed/ProductSlider', {
        _init: function () {
            this._super();

            this._isInitialized = false;
            this._visibleIndex = 0;

            this._loadedCount = 0;
            this._fakeCount = 0;
            this._duplicateCount = 0;
            this._visibleCount = 0;

            this._collectionIds = null;

            this._originalItemWidth = 0;
            this._originalPaddingWidth = 0;
            this._containerWidth = 0;

            this._itemInnerWidth = 0;
            this._itemOuterWidth = 0;
            this._mode = '';

            // obsolete
            this._itemWidth = 0;
            this._currentItemWidth = 0;
            this._paddingWidth = 0;
        },
        _initialize: function() {
            if (!this._isInitialized) {
                var $li = this.$loadedFakeAndDuplicatedElements();
                var self = this;
                this._isInitialized = true;

                this._loadedCount = $li.length;
                if (!this._loadedCount) {
                    return;
                }

                this._originalItemWidth = $li.outerWidth(true);
                this._originalPaddingWidth = $li.outerWidth(true) - $li.width();

                if (this._loadedCount < this.getCollectionIds().length) {
                    this._addFakeItems($li, this.getCollectionIds().length - this._loadedCount);
                    $li = this.$loadedFakeAndDuplicatedElements();
                    ajax.post(this.getUrl(), {"xml": this.getXml() }, function(response) {
                        self.loadAjaxItems(response);
                    }, { showOverlay: false, showWait: false});
                }
            }
        },
        _subscribeToBlockEvents: function() {
            return this
                ._super()
                .on('resize', this, this.resize)
        },
        _subscribeToHtmlEvents: function () {
            var self = this;

            function _next() { self.next(); return false; }
            function _previous() { self.previous(); return false; }

            return this
                ._super()
                .on('bind', this, function () {
                    //this.resize();
                    this.$().find('.m-navigation-next').on('click', _next);
                    this.$().find('.m-navigation-prev').on('click', _previous);
                })
                .on('unbind', this, function () {
                    this.$().find('.m-navigation-next').off('click', _next);
                    this.$().find('.m-navigation-prev').off('click', _previous);
                });
        },
        resize: function() {
            var self = this;
            this._initialize();
            var $li = this.$loadedFakeAndDuplicatedElements();

            if (!this._loadedCount) {
                return;
            }

            this._containerWidth = this.$().width();
            this._visibleCount = Math.floor(this._containerWidth / this._originalItemWidth);
            this._mode = (this._visibleCount >= this.getCollectionIds().length) ? 'center' : 'slide';

            if (this._mode == 'slide') {
                var neededDuplicateCount = this._getDuplicatesNeededCount();
                if (neededDuplicateCount > 0) {
                    if (this._duplicateCount == 0) {
                        this._addDuplicateItems($li, neededDuplicateCount);
                        $li = this.$loadedFakeAndDuplicatedElements();
                    }
                }
                else {
                    if (this._duplicateCount != 0) {
                        this._removeDuplicateItems($li);
                        $li = this.$loadedFakeAndDuplicatedElements();
                    }
                }
            }
            else {
                if (this._duplicateCount != 0) {
                    this._removeDuplicateItems($li);
                    $li = this.$loadedFakeAndDuplicatedElements();
                }
            }

            // do resize everyhting
            this._itemInnerWidth = this._mode == 'slide'
                ? Math.floor(this._containerWidth / this._visibleCount - this._originalPaddingWidth)
                : this._originalItemWidth - this._originalPaddingWidth;
            this._itemOuterWidth = Math.ceil(this._containerWidth / this._visibleCount);
            $li.each(function () {
                self.resizeLiItem ($(this));
            });
            this.$products().width(this._itemOuterWidth * $li.length);

        },
        loadAjaxItems: function(response) {
            var self = this;
            var $ajaxUl = $('<ul>' + response + '</ul>');
            $ajaxUl.children().each(function() {
                var ajaxLi = this;
                var index = core.getPrefixedClass(this, 'item-');
                self.resizeLiItem($(this));
                self.$().find('li.item-' + index).each(function() {
                    $(this).replaceWith($(ajaxLi).clone());
                });
            });
        },
        resizeLiItem: function ($liItem) {
            $liItem.width(this._itemInnerWidth);
            $liItem.find('.product-image').width(this._itemInnerWidth).height(this._itemInnerWidth);
            $liItem.find('.product-image img').attr('width', this._itemInnerWidth).attr('height', this._itemInnerWidth);
            $liItem.find('.actions').width(this._itemInnerWidth);
        },
        $products: function() {
            return this.$().find('ul.products-grid');
        },
        /**
         * IDs of all items in server collection
         * @returns number
         */
        getCollectionIds: function() {
            if (!this._collectionIds) {
                this._collectionIds = json.decodeAttribute(this.$().data('ids'));
            }
            return this._collectionIds;
        },
        getEffectDuration: function() {
            if (!this._effectDuration) {
                this._effectDuration = this.$().data('effect-duration');
            }
            return this._effectDuration;
        },
        getWidth: function() {
            if (!this._width) {
                this._width = this.$().data('width');
            }
            return this._width;
        },
        getUrl: function() {
            if (!this._url) {
                this._url = urlTemplate.decodeAttribute(this.$().data('url'));
            }
            return this._url;
        },
        getXml: function() {
            if (!this._xml) {
                this._xml = this.$().data('xml');
            }
            return this._xml;
        },
        /**
         * All loaded, fake and duplicated items
         * @returns jQuery
         */
        $loadedFakeAndDuplicatedElements: function() {
            return this.$().find('li.item');
        },
        _getMaxCount: function() {
            var collectionCount = this.getCollectionIds().length;
            return !this._areDuplicatesNeeded() ? collectionCount : collectionCount * 2;
        },
        _areDuplicatesNeeded: function () {
            return this.getCollectionIds().length < this._visibleCount * 2;
        },
        _getDuplicatesNeededCount: function() {
            var count;
            count = this._visibleCount * 2 - this.getCollectionIds().length;
            if (!this._areDuplicatesNeeded()) {
                count = 0;
            }
            else {
                count = this.getCollectionIds().length;
            }
            return count;
        },
        _addFakeItems: function($li, count) {
            for (var i = 0; i < count; i++) {
                this.$products().append(this._createFakeItem(i + this._loadedCount + this._fakeCount));
            }
            this._fakeCount += count;
        },
        _addDuplicateItems: function ($li, count) {
            for (var i = 0; i < count; i++) {
                this.$products().append(this._createDuplicateItem($li, i, i + this._loadedCount + this._fakeCount + this._duplicateCount));
            }
            this._duplicateCount += count;

        },
        _removeDuplicateItems: function ($li) {
//            throw 'Not implemented';
        },
        _createFakeItem: function (index) {
            return  $('<li class="item item-' + index + '"> </li>');
        },
        _createDuplicateItem: function ($li, indexFrom, indexTo) {
            return $li[indexFrom].clone(true);
        },
        getIndex: function(startIndex) {
            var length = this._getMaxCount();
            return (startIndex + length) % length;
        },
        next: function () {
            var $li = this.$loadedFakeAndDuplicatedElements();
            var nextVisibleIndex = this._visibleIndex + this._visibleCount;
            var rotateCount = nextVisibleIndex + this._visibleCount - $li.length;
            if (rotateCount > 0) {
                var liArray = $.makeArray($li);
                for (var i = 0; i < rotateCount; i++) {
                    this.$products().append(liArray[i]);
                }
                this._visibleIndex -= rotateCount;
                nextVisibleIndex -= rotateCount;
                this.$products().animate({left: "+=" + (this._itemOuterWidth * rotateCount)}, 0);
            }

            this._visibleIndex = this.getIndex(nextVisibleIndex);
            this.$products().animate({left: "-=" + (this._itemOuterWidth * this._visibleCount)},
                this.getEffectDuration());
        },
        previous: function () {
            var $li = this.$loadedFakeAndDuplicatedElements();
            var nextVisibleIndex = this._visibleIndex - this._visibleCount;
            var rotateCount = -nextVisibleIndex;
            if (rotateCount > 0) {
                var liArray = $.makeArray($li);
                for (var i = 0; i < rotateCount; i++) {
                    this.$products().prepend(liArray[liArray.length - i - 1]);
                }
                this._visibleIndex += rotateCount;
                nextVisibleIndex += rotateCount;
                this.$products().animate({left: "-=" + (this._itemOuterWidth * rotateCount)}, 0);
            }

            this._visibleIndex = this.getIndex(nextVisibleIndex);
            this.$products().animate({left: "+=" + (this._itemOuterWidth * this._visibleCount)},
                this.getEffectDuration());
        },
        animate: function(transformX, duration) {
            this.$products().animate({ transformX: transformX}, {
                    step: function(now) {
                        var x = now ? now + 'px' : '0';
                        $(this).css({
                            'transform': 'translate(' + x + ', 0)',
                            '-ms-transform': 'translate(' + x + ', 0)',
                            '-webkit-transform': 'translate(' + x + ', 0)'
                        });
                    },
                    duration: duration
                },
                this.getEffectDuration());
        }
    });
});

Mana.define('ManaSlider/Tabbed/Slider', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Layout'],
function ($, Block, layout) {
    return Block.extend('ManaSlider/Tabbed/Slider', {
        _subscribeToHtmlEvents: function () {
            var self = this;
            return this
                ._super()
                .on('bind', this, function () {
                    var $this = this.$();
                    if ($this.tabs) {
                        $this.tabs({
                            activate: function (event, ui) {
                                self.trigger('resize', {}, false, true);
                            }
                        });
                    }
                })
                .on('unbind', this, function () {

                });
        }
    });
});