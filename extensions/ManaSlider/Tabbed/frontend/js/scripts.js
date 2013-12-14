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

            this._inSliding =false;
            this._rotationTimer = null;

            // obsolete
            this._itemWidth = 0;
            this._currentItemWidth = 0;
            this._paddingWidth = 0;

            this.SLIDE_TOUCH_DISTANCE = 10;

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
                this.equalizeHeights();
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
            function _beginTouch(e) { self.beginTouch(e); }
            function _endTouch(e) { self.endTouch(e); }

            return this
                ._super()
                .on('bind', this, function () {
                    //this.resize();
                    //this._transformX = 0;

                    this.$().find('.m-navigation-next').on('click', _next);
                    this.$().find('.m-navigation-prev').on('click', _previous);
                    this.$().on('mousedown', _beginTouch);
                    this.$().on('mouseup', _endTouch);
                    this.$().on('touchstart', _beginTouch);
                    this.$().on('touchend', _endTouch);
                })
                .on('unbind', this, function () {
                    this.$().find('.m-navigation-next').off('click', _next);
                    this.$().find('.m-navigation-prev').off('click', _previous);
                    this.$().off('mousedown', _beginTouch);
                    this.$().off('mouseup', _endTouch);
                    this.$().off('touchstart', _beginTouch);
                    this.$().off('touchend', _endTouch);
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
                this.$().find('.m-navigation-next').show();
                this.$().find('.m-navigation-prev').show();
            }
            else {
                if (this._duplicateCount != 0) {
                    this._removeDuplicateItems($li);
                    $li = this.$loadedFakeAndDuplicatedElements();
                }
                this.$().find('.m-navigation-next').hide();
                this.$().find('.m-navigation-prev').hide();
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
            if (this._mode == 'slide') {
                this.$products().css({ left : - this._itemOuterWidth * this._visibleIndex });
            }
            this.equalizeHeights();
            this.scheduleRotation();
            if (this._mode == 'slide') {
                this.moveMissingElementsToTheEnd();
            }

        },
        loadAjaxItems: function(response) {
            var self = this;
            var $ajaxUl = $('<ul>' + response + '</ul>');
            $ajaxUl.children().each(function() {
                var ajaxLi = this;
                var index = core.getPrefixedClass(this, 'item-');
                self.resizeLiItem($(this));
                self.$().find('li.item-' + index).each(function() {
                    if ($(this).hasClass('li-duplicate')) {
                        $(ajaxLi).addClass("li-duplicate");
                    }
                    $(this).replaceWith($(ajaxLi).clone());
                });
            });
            this.equalizeHeights();
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
        getRotationDuration: function () {
            if (!this._rotationDuration) {
                this._rotationDuration = this.$().data('rotation-duration');
            }
            return this._rotationDuration;
        },
        getSlideCount: function () {
            if (!this._slideCounth) {
                this._slideCounth = this.$().data('slide-count');
            }
            return this._slideCounth;
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
            $li.detach('.li-duplicate');
            this._duplicateCount = 0;
            if (this._visibleIndex >= this.getCollectionIds().length) {
                this._visibleIndex -= this.getCollectionIds().length;
            }
        },
        _createFakeItem: function (index) {
            return  $('<li class="item item-' + index + '"> </li>');
        },
        _createDuplicateItem: function ($li, indexFrom, indexTo) {
//            return $li[indexFrom].clone(true).addClass('Duplicate');

            var $duplicatedLi= $li.eq(indexFrom).clone(true);
            $duplicatedLi.addClass("li-duplicate");
            return $duplicatedLi;
        },
        getIndex: function(startIndex) {
            var length = this._getMaxCount();
            return (startIndex + length) % length;
        },
        moveMissingElementsToTheEnd: function() {
            var missingCount = this._visibleIndex + this._visibleCount - this.getCollectionIds().length;
            if (missingCount > 0) {
                var $li = this.$loadedFakeAndDuplicatedElements();
                var liArray = $.makeArray($li);
                for (var i = 0; i < missingCount; i++) {
                    this.$products().append(liArray[i]);
                }
                this._visibleIndex -= missingCount;
                this.animate({left: "+=" + (this._itemOuterWidth * missingCount)}, 0);
            }
        },
        next: function () {
            if (this._inSliding) {
                return;
            }
            this._inSliding = true;
            this.scheduleRotation({disable: true});
            var $li = this.$loadedFakeAndDuplicatedElements();
            var slideWidth = (this.getSlideCount() && this.getSlideCount() < this._visibleCount)
                ? this.getSlideCount()
                :  this._visibleCount;
            var nextVisibleIndex = this._visibleIndex + slideWidth;
            var rotateCount = nextVisibleIndex + this._visibleCount - $li.length;
            if (rotateCount > 0) {
                var liArray = $.makeArray($li);
                for (var i = 0; i < rotateCount; i++) {
                    this.$products().append(liArray[i]);
                }
                this._visibleIndex -= rotateCount;
                nextVisibleIndex -= rotateCount;
                this.animate({left: "+=" + (this._itemOuterWidth * rotateCount)}, 0);
            }

            this._visibleIndex = this.getIndex(nextVisibleIndex);
            this.animate({left: "-=" + (this._itemOuterWidth * slideWidth)}, this.getEffectDuration(), true);

        },
        previous: function () {
            if (this._inSliding) {
                return;
            }
            this._inSliding = true;
            this.scheduleRotation({disable: true});
            var $li = this.$loadedFakeAndDuplicatedElements();
            var slideWidth = (this.getSlideCount() && this.getSlideCount() < this._visibleCount)
                ? this.getSlideCount()
                : this._visibleCount;
            var nextVisibleIndex = this._visibleIndex - slideWidth;
            var rotateCount = -nextVisibleIndex;
            if (rotateCount > 0) {
                var liArray = $.makeArray($li);
                for (var i = 0; i < rotateCount; i++) {
                    this.$products().prepend(liArray[liArray.length - i - 1]);
                }
                this._visibleIndex += rotateCount;
                nextVisibleIndex += rotateCount;
                this.animate({left: "-=" + (this._itemOuterWidth * rotateCount)}, 0);
            }

            this._visibleIndex = this.getIndex(nextVisibleIndex);
            this.animate({left: "+=" + (this._itemOuterWidth * slideWidth)},this.getEffectDuration(), true);
        },
        animate: function(properties, duration, clearInSlidingFlsg) {

            var self = this;
            this.$products().animate(properties, duration, function() {
                self.scheduleRotation();
                if (clearInSlidingFlsg) {
                    self._inSliding = false;
                    if (self._mode == 'slide') {
                        self.$products().css({ left: -self._itemOuterWidth * self._visibleIndex });
                    }
                }
            });
        },
        equalizeHeights: function() {
            var height = 0;
            var $li = this.$loadedFakeAndDuplicatedElements();
            $li.height('auto');
            $li.each(function () {
                var liHeight = $(this).height();
                if (liHeight > height) {
                    height = liHeight;
                }
            });
            $li.height(height);
        },
        beginTouch: function(e) {
            this._touchX = e.originalEvent && e.originalEvent.touches ? e.originalEvent.touches[0].pageX : e.pageX;
        },
        endTouch: function (e) {
            var touchX = e.originalEvent && e.originalEvent.changedTouches ? e.originalEvent.changedTouches[0].pageX : e.pageX;
            var distance = touchX - this._touchX;
            if (Math.abs(distance) > this.SLIDE_TOUCH_DISTANCE) {
                if (distance > 0) {
                    this.previous();
                }
                else {
                    this.next();
                }
            }
        },
        scheduleRotation: function(options) {
            var self = this;
            var disable = options && options.disable;
            if (!disable) {
                var $parent = this.$().parent();
                disable = $parent.hasClass('ui-tabs-panel') && !$parent.is(':visible');
            }

            function _timer() {
                self._rotationTimer = null;
                self.next();
            }

            if (!disable && this.getRotationDuration() > 0 && this._mode == 'slide') {
                if (!this._rotationTimer) {
                    this._rotationTimer = setTimeout(_timer, this.getRotationDuration());
                }
            }
            else {
                if (this._rotationTimer) {
                    clearTimeout(this._rotationTimer);
                    this._rotationTimer = null;
                }
            }

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