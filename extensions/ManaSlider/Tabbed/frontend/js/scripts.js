/**
 * @category    Mana
 * @package     ManaSlider_Tabbed
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('ManaSlider/Tabbed/ProductSlider', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Json',
    'singleton:Mana/Core/Ajax'],
function ($, Block, json, ajax)
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
//                    throw 'Not implemented';
//                    ajax.get('url', function(response) {
//                        self.loadAjaxItems(response);
//                    }, { showOverlay: false, showWait: false});

                    this._addFakeItems($li, this.getCollectionIds().length - this._loadedCount);
                }
            }
        },
        _subscribeToBlockEvents: function() {
            return this
                ._super()
                .on('resize', this, this.resize);
        },
        _subscribeToHtmlEvents: function () {
            var self = this;

            function _next() { self.next(); return false; }
            function _previous() { self.previous(); return false; }

            return this
                ._super()
                .on('bind', this, function () {
                    //this.resize();
                    this.$().find('.next').on('click', _next);
                    this.$().find('.previous').on('click', _previous);
                })
                .on('unbind', this, function () {
                    this.$().find('.next').off('click', _next);
                    this.$().find('.previous').off('click', _previous);
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
                if (this._areDuplicatesNeeded()) {
                    if (this._duplicateCount == 0) {
                        this._addDuplicateItems($li);
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
                : this._originalItemWidth;
            this._itemOuterWidth = Math.ceil(this._containerWidth / this._visibleCount);
            $li.each(function () {
                $(this).width(self._itemInnerWidth);
                $(this).find('.product-image').width(self._itemInnerWidth).height(self._itemInnerWidth);
                $(this).find('.product-image img').attr('width', self._itemInnerWidth).attr('height', self._itemInnerWidth);
                $(this).find('.actions').width(self._itemInnerWidth);
            });
            this.$products().width(this._itemOuterWidth * $li.length);

            // add items to the left
//            startIndex = this._visibleIndex;
//            endIndex = this.getPrevIndex(startIndex);
//            for (var i = startIndex; i > endIndex; i--) {
//                var newIndex = this.getCollectionCount() -1 + i;
//                $newItem = this._createFakeItem(newIndex, itemWidth, itemHeight);
//                $li.splice(0, 0, $newItem);
//                this.$products().prepend($newItem);
//            }

        },
        loadAjaxItems: function(response) {
            throw 'Not implemented';
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
        _areDuplicatesNeeded: function() {
            return this.getCollectionIds().length < this._visibleCount * 2;
        },
        _addFakeItems: function($li, count) {
            for (var i = 0; i < count; i++) {
                this.$products().append(this._createFakeItem(i + this._loadedCount + this._fakeCount));
            }
            this._fakeCount += count;
        },
        _addDuplicateItems: function ($li) {
            throw 'Not implemented';
        },
        _removeDuplicateItems: function ($li) {
            throw 'Not implemented';
        },
        _createFakeItem: function (index) {
            return  $('<li class="item item-' + index + '"> </li>');
        },
        getIndex: function(startIndex) {
            return (startIndex + this.getCollectionIds().length) % this.getCollectionIds().length;
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
        }
    });
});