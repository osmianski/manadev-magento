/**
 * @category    Mana
 * @package     ManaPro_FilterPositioning
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */


; // make JS merging easier

Mana.define('Mana/LayeredNavigation/Top', ['jquery', 'Mana/Core/Block'],
function($, Block, undefined)
{
    return Block.extend('Mana/LayeredNavigation/Top', {
        _init: function () {
            this._super();
            this._expandCollapseStates = {};
            this._accordionExpandedId = undefined;
            this._minHeights = {};
            this._expandCollapseEnabled = false;
            this._subTitleExpanded = false;
        },
        _subscribeToHtmlEvents: function () {
            var self = this;
            function _expandCollapse() {
                self.expandCollapse(this);
            }
            function _expandCollapseSubTitle() {
                self.expandCollapseSubTitle(this);
            }
            function _mouseEnter() {
                self.$().addClass('m-over');
            }
            function _mouseLeave() {
                self.$().removeClass('m-over');
            }

            return this
                ._super()
                .on('bind', this, function () {
                    if (!this.$().hasClass("one-filter-column")) {
                        this._expandAll();
                    }
                    this.$().find('dl dt.m-ln').on('click', _expandCollapse);
                    this.$().find('.block-subtitle').on('click', _expandCollapseSubTitle);
                    this.$().find('.block-subtitle, .actions').on('mouseenter', _mouseEnter);
                    this.$().find('.block-subtitle, .actions').on('mouseleave', _mouseLeave);
                })
                .on('unbind', this, function () {
                    this.$().find('dl dt.m-ln').off('click', _expandCollapse);
                    this.$().find('.block-subtitle').off('click', _expandCollapseSubTitle);
                    this.$().find('.block-subtitle, .actions').off('mouseenter', _mouseEnter);
                    this.$().find('.block-subtitle, .actions').off('mouseleave', _mouseLeave);
                });
        },
        _subscribeToBlockEvents: function() {
            return this
                ._super()
                .on('resize', this, this.resize);
        },
        resize: function() {
            var self = this;
            var widthClassFound = false;
            var wasOneColumn = this.$().hasClass("one-filter-column");
            $.each(this.getWidths(), function(cls, width) {
                if (width && !widthClassFound && self.$().width() < width) {
                    self.$().addClass(cls);
                    widthClassFound = true;
                }
                else {
                    self.$().removeClass(cls);
                }
            });
            var isOneColumn = this.$().hasClass("one-filter-column");
            if (wasOneColumn && !isOneColumn) {
                this._expandAll();
            }
            else if (isOneColumn && !wasOneColumn) {
                this._expandCollapseAll();
            }
        },
        expandCollapse: function(dt) {
            var id = $(dt).data('id');
            if (this._expandCollapseEnabled) {
                switch (this.getExpandCollapseBehavior()) {
                    case 'initially-collapsed':
                        if (this._expandCollapseStates[id]) {
                            this._expandCollapseStates[id] = false;
                            this.collapse(dt, this.getExpandCollapseDuration());
                        }
                        else {
                            this._expandCollapseStates[id] = true;
                            this.expand(dt, this.getExpandCollapseDuration());
                        }
                        break;
                    case 'initially-expanded':
                        if (this._expandCollapseStates[id]) {
                            this._expandCollapseStates[id] = false;
                            this.expand(dt, this.getExpandCollapseDuration());
                        }
                        else {
                            this._expandCollapseStates[id] = true;
                            this.collapse(dt, this.getExpandCollapseDuration());
                        }
                        break;
                    case 'accordion':
                    default:
                        if (this._accordionExpandedId != id) {
                            this.collapse(this.$().find('dl dt.m-ln[data-id="' + this._accordionExpandedId + '"]'), this.getExpandCollapseDuration());
                            this.expand(dt, this.getExpandCollapseDuration());
                            this._accordionExpandedId = id;
                        }
                        else if (this._accordionExpandedId !== '') {
                            this.collapse(this.$().find('dl dt.m-ln[data-id="' + this._accordionExpandedId + '"]'), this.getExpandCollapseDuration());
                            this._accordionExpandedId = '';
                        }
                        break;
                }
            }
        },
        expandCollapseSubTitle: function() {
            if (this._expandCollapseEnabled) {
                if (this._subTitleExpanded) {
                    this._subTitleExpanded = false;
                    this.collapseSubTitle(this.getExpandCollapseDuration());
                }
                else {
                    this._subTitleExpanded = true;
                    this.expandSubTitle(this.getExpandCollapseDuration());
                }
            }
        },
        _expandCollapseAll: function() {
            this._expandCollapseEnabled = true;
            var $filters = this.$().find('dl dt.m-ln');
            var self = this;
            $filters.each(function () {
                var minHeight;
                if (minHeight = $(this).parent().css('min-height')) {
                    self._minHeights[$(this).data('id')] = minHeight;
                    $(this).parent().css('min-height', '');
                }
            });

            switch (this.getExpandCollapseBehavior()) {
                case 'initially-collapsed':
                    this._expandCollapseAllByDefaultCollapsed();
                    break;
                case 'initially-expanded':
                    this._expandCollapseAllByDefaultExpanded();
                    break;
                case 'accordion':
                default:
                    this._expandCollapseAllAsAccordion();
                    break;
            }
            if (this._subTitleExpanded) {
                this.expandSubTitle(0);
            }
            else {
                this.collapseSubTitle(0);
            }
        },
        _expandCollapseAllByDefaultExpanded: function() {
            var $filters = this.$().find('dl dt.m-ln');
            var self = this;
            $filters.each(function () {
                if (self._expandCollapseStates[$(this).data('id')]) {
                    self.collapse(this, 0);
                }
                else {
                    self.expand(this, 0);
                }
            });
        },
        _expandCollapseAllByDefaultCollapsed: function() {
            var $filters = this.$().find('dl dt.m-ln');
            var self = this;
            $filters.each(function () {
                if (self._expandCollapseStates[$(this).data('id')]) {
                    self.expand(this, 0);
                }
                else {
                    self.collapse(this, 0);
                }
            });
        },
        _expandAll: function() {
            this._expandCollapseEnabled = false;
            var $filters = this.$().find('dl dt.m-ln');
            var self = this;
            $filters.each(function () {
                var minHeight;
                if (minHeight = self._minHeights[$(this).data('id')]) {
                    $(this).parent().css('min-height', minHeight);
                }
                self.expand(this, 0);
            });
            this.expandSubTitle(0);
        },
        _expandCollapseAllAsAccordion: function() {
            var $filters = this.$().find('dl dt.m-ln');
            var self = this;
            var firstFilterId = '';
            var found = false;
            $filters.each(function () {
                var id = $(this).data('id');
                if (!firstFilterId) {
                    firstFilterId = id;
                }
                if (self._accordionExpandedId == id) {
                    found = true;
                    return false;
                }
                else {
                    return true;
                }
            });
            if (!found && self._accordionExpandedId !== '') {
                self._accordionExpandedId = firstFilterId;
            }
            $filters.each(function () {
                if (self._accordionExpandedId == $(this).data('id')) {
                    self.expand(this, 0);
                }
                else {
                    self.collapse(this, 0);
                }
            });
        },
        getExpandCollapseBehavior: function() {
            if (this._expandCollapseBehavior === undefined) {
                this._expandCollapseBehavior = this.$().data('expand-collapse-behavior');
            }
            return this._expandCollapseBehavior;
        },
        getExpandCollapseDuration: function() {
            if (this._expandCollapseDuration === undefined) {
                this._expandCollapseDuration = this.$().data('expand-collapse-duration');
                if (this._expandCollapseDuration === '') {
                    this._expandCollapseDuration = 500;
                }
            }
            return this._expandCollapseDuration;
        },
        getWidths: function() {
            if (this._widths === undefined) {
                this._widths = {
                    "one-filter-column": this.$().data('one-column'),
                    "two-filter-columns": this.$().data('two-columns'),
                    "three-filter-columns": this.$().data('three-columns'),
                    "four-filter-columns": this.$().data('four-columns')
                };
            }
            return this._widths;
        },
        expand: function (element, duration) {
            $(element).removeClass('m-collapsed').addClass('m-expanded');
            this._fixSliderWidth(element);
            $(element).next().slideDown(duration);
        },
        _fixSliderWidth: function(dt) {
            var resize;
            var id = $(dt).data('id');
            var match = id.match(/m_(.*)_filter/);
            if (match[1] && (resize = _mana_oldResizehandler[match[1]])) {
                resize();
            }
        },
        collapse: function (element, duration) {
            $(element).removeClass('m-expanded').addClass('m-collapsed');
            $(element).next().slideUp(duration);
        },
        expandSubTitle: function (duration) {
            var self = this;
            this.$().removeClass('m-collapsed').addClass('m-expanded');
            this.$().find('.block-subtitle').removeClass('m-collapsed').addClass('m-expanded');
            this.$().find('dl dt.m-ln').each(function() {
                self._fixSliderWidth(this);
            });
            $('.m-shop-by').next().slideDown(duration);
        },
        collapseSubTitle: function (duration) {
            this.$().removeClass('m-expanded').addClass('m-collapsed');
            this.$().find('.block-subtitle').removeClass('m-expanded').addClass('m-collapsed');
            $('.m-shop-by').next().slideUp(duration);
        }
});
});

(function($) {
    function _width(dt, dd) {
        var maxWidth = dd.attr('data-max-width');
        var result = dd.width() > dt.width() ? dd.width() : dt.width();
        return maxWidth ? (result <= maxWidth ? result : maxWidth) : result;
    }

  $('.col-main div.block-layered-nav.m-topmenu dl dt.m-ln')
    .live('mouseover', function() {
        if ($(this).parent().hasClass('m-inline')) {
            return true;
        }

        var dt = $(this);
        var dd = $(this).next();
        dd
          .removeClass('hidden')
          .offset({
            top: dt.offset().top + dt.outerHeight(),
            left: dt.offset().left
          })
          .width(_width(dt, dd))
          .addClass('m-popup-filter');
        dt
          .addClass('m-popup-filter');
      })
    .live('mouseout', function() {
        if ($(this).parent().hasClass('m-inline')) {
            return true;
        }

        var dt = $(this);
        var dd = $(this).next();
        dd
          .removeClass('m-popup-filter')
          .addClass('hidden');
        dt
          .removeClass('m-popup-filter');
      });
  $('.col-main div.block-layered-nav.m-topmenu dl dd.m-ln')
    .live('mouseover', function() {
        if ($(this).parent().hasClass('m-inline')) {
            return true;
        }

        var dd = $(this);
        var dt = $(this).prev();
        dd
          .removeClass('hidden')
          .offset({
            top: dt.offset().top + dt.outerHeight(),
            left: dt.offset().left
          })
          .width(_width(dt, dd))
          .addClass('m-popup-filter');
        dt
          .addClass('m-popup-filter');
      })
      .live('mouseout', function() {
        if ($(this).parent().hasClass('m-inline')) {
            return true;
        }

        var dd = $(this);
        var dt = $(this).prev();
        dd
          .removeClass('m-popup-filter')
          .addClass('hidden');
        dt
          .removeClass('m-popup-filter');

      });
    
})(jQuery);