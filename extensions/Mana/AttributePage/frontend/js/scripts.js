/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('Mana/AttributePage/AlphaList2', ['jquery', 'Mana/Core/Block'], function ($, Block) {
    return Block.extend('Mana/AttributePage/AlphaList2', {
        _subscribeToHtmlEvents:function () {
            var self = this;
            function _click () {
                self.click($(this).html());
                return false;
            }

            return this
                ._super()
                .on('bind', this, function () {
                    if (this.$().is('.no-paging')) {
                        this.$().on('click', '.m-alpha-pager a', _click);
                    }
                })
                .on('unbind', this, function () {
                    if (this.$().is('.no-paging')) {
                        this.$().off('click', '.m-alpha-pager a', _click);
                    }
                });
        },
        _subscribeToBlockEvents: function () {
            return this
                ._super()
                .on('load', this, function () {
                    this.on('resize', this, this.resize);
                })
                .on('unload', this, function () {
                    this.off('resize', this, this.resize);
                });
        },
        click: function(alpha) {
            this._alpha = alpha;
            this._calculate(alpha, 400);
        },
        resize: function() {
            if (this._alpha) {
                this._calculate(this._alpha);
            }
        },
        _calculate: function(alpha, duration) {
            var $container = $('#m-option-page-list');;
            var $content = $container.children().first();
            var $alphaList = $container.find("div[data-alpha='" + alpha + "']").parent();

            // container is always in fixed place, we use is as anchor in our coordinate system
            var containerTop = $container.offset().top;

            // content top says how much it scrolled. It is always 0 ir negative
            var contentTop = $content.offset().top;

            // this top should become the same as container top after scroll operation
            var alphaListTop = $alphaList.offset().top;

            if (duration) {
                $content.animate({top: contentTop - alphaListTop}, duration);
                $container.animate({height: $content.outerHeight() + (contentTop - alphaListTop)}, duration);
            }
            else {
                $content.css({top: contentTop - alphaListTop});
                $container.css({height: $content.outerHeight() + (contentTop - alphaListTop)});
            }
        }

    });
});
