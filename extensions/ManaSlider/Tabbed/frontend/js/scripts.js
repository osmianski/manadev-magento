/**
 * @category    Mana
 * @package     ManaSlider_Tabbed
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('ManaSlider/Tabbed/ProductSlider', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Json'],
function ($, Block, json)
{
    return Block.extend('ManaSlider/Tabbed/ProductSlider', {
        _init: function () {
            this._super();
            this._itemWidth = 0;
            this._currentItemWidth = 0;
            this._paddingWidth = 0;
            this._columnCount = 0;
            this._ids = null;
            this._idCount = 0;
            this._index = 0;
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
            var $slider = this.$();
            var $items = $slider.find('li.item');
            if (!$items.length) {
                return;
            }

            this.getIds();

            if (!this._itemWidth) {
                this._itemWidth = $items.outerWidth(true);
                this._paddingWidth = $items.outerWidth(true) - $items.width();
            }
            var itemWidth = this._itemWidth;
            var width = $slider.width();
            this._columnCount = Math.floor(width/itemWidth);
            var newItemWidth = Math.floor(width / this._columnCount - this._paddingWidth);
            var newItemHeight = 0;
            this._currentItemWidth = Math.ceil(width / this._columnCount);

            $items.each(function () {
                $(this).width(newItemWidth);
                $(this).find('.product-image').width(newItemWidth).height(newItemWidth);
                $(this).find('.product-image img').attr('width', newItemWidth).attr('height', newItemWidth);
                $(this).find('.actions').width(newItemWidth);
                newItemHeight = $(this).height();
            });

            // add items to the right
            var startIndex = this.getNextIndex(this._index);
            var endIndex = this.getNextIndex(startIndex);
            for (var i = $items.length; i < endIndex; i++) {
                var $newItem = this.getNewItem (i, newItemWidth, newItemHeight);
                $items.push($newItem);
                this.$products().append($newItem);
            }
            // add items to the left
            var startIndex = this._index;
            var endIndex = this.getPrevIndex(startIndex);
            for (var i = startIndex; i > endIndex; i--) {
                var newIndex = this._idCount -1 + i;
                var $newItem = this.getNewItem(newIndex, newItemWidth, newItemHeight);
//                $items.unshift($newItem);
                $items.push($newItem);
                this.$products().prepend($newItem);
            }
            this.$products().width(this._currentItemWidth * $items.length);

        },
        $products: function() {
            return this.$().find('ul.products-grid');
        },
        getIds: function() {
            if (!this._ids) {
                this._ids = json.decodeAttribute(this.$().data('ids'));
                this._idCount = 26;
            }
            return this._ids;
        },
        getEffectDuration: function() {
            if (!this._effectDuration) {
                this._effectDuration = this.$().data('effect-duration');
            }
            return this._effectDuration;
        },
        getCount: function() {
            return this._ids.length;
        },
        getNewItem: function (index, width, height) {
            return  $('<li class="item item-' + index + '" style="width: ' + width + 'px; height: ' + height + 'px;"> </li>');
        },
        getNextIndex: function(startIndex) {
            return (startIndex + this._columnCount) % this._idCount;
        },
        getPrevIndex: function (startIndex) {
            return (startIndex + this._index - this._columnCount) % this._idCount;
        },
        next: function () {
            var width = this._currentItemWidth * this._columnCount;
            //var newIndex = this.getNextIndex();
            //this._index = newIndex;
  /*          var status = this.getItemStatus(0, this._columnCount);
            if (status.status == 'not_loaded') {
                this.load(status.missingIds, function() {
                    this.arrange();

                });
                return;
            }
            else if (status.status == 'not_enough') {
                this.duplicate();
                this.arrange();
            }
            else if (status.status == 'not_arranged') {
                this.arrange();
            }
            */
            this.$products().animate({left: "-=" + width}, this.getEffectDuration());
        },
        previous: function () {
            var width = this._currentItemWidth * this._columnCount;
           // var newIndex = this.getPrevIndex();
           //this._index = newIndex;
            this.$products().animate({left: "+=" + width}, this.getEffectDuration());
        },
        areItemsInPlace: function(relativeIndex, count) {
            return;
        }
    });
});