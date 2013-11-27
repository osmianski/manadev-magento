/**
 * @category    Mana
 * @package     ManaPro_FilterPositioning
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */


; // make JS merging easier

Mana.define('Mana/LayeredNavigation/TopBlock', ['jquery', 'Mana/Core/Block'],
function($, Block)
{
    return Block.extend('Mana/LayeredNavigation/TopBlock', {
        _init: function () {
            this._super();
            this._expandCollapseStates = {};
            this._accordionExpandedId = undefined;
            this._mobileLayout = false;
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
                        this._prepareWideLayout();
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
                this._prepareWideLayout();
            }
            else if (isOneColumn && !wasOneColumn) {
                this._prepareMobileLayout();
            }
        },
        expandCollapse: function(dt) {
            var id = $(dt).data('id');
            if (this._mobileLayout) {
                switch (this.getBehavior()) {
                    case 'initially-collapsed':
                        if (this._expandCollapseStates[id]) {
                            this._expandCollapseStates[id] = false;
                            this.collapse(dt, this.getDuration());
                        }
                        else {
                            this._expandCollapseStates[id] = true;
                            this.expand(dt, this.getDuration());
                        }
                        break;
                    case 'initially-expanded':
                        if (this._expandCollapseStates[id]) {
                            this._expandCollapseStates[id] = false;
                            this.expand(dt, this.getDuration());
                        }
                        else {
                            this._expandCollapseStates[id] = true;
                            this.collapse(dt, this.getDuration());
                        }
                        break;
                    case 'accordion':
                    default:
                        if (this._accordionExpandedId != id) {
                            this.collapse(this.$().find('dl dt.m-ln[data-id="' + this._accordionExpandedId + '"]'), this.getDuration());
                            this.expand(dt, this.getDuration());
                            this._accordionExpandedId = id;
                        }
                        else if (this._accordionExpandedId !== '') {
                            this.collapse(this.$().find('dl dt.m-ln[data-id="' + this._accordionExpandedId + '"]'), this.getDuration());
                            this._accordionExpandedId = '';
                        }
                        break;
                }
            }
        },
        expandCollapseSubTitle: function() {
            if (this._mobileLayout) {
                if (this._subTitleExpanded) {
                    this._subTitleExpanded = false;
                    this.collapseSubTitle(this.getDuration());
                }
                else {
                    this._subTitleExpanded = true;
                    this.expandSubTitle(this.getDuration());
                }
            }
        },
        _prepareMobileLayout: function() {
            this._mobileLayout = true;
            this.$().removeClass('m-wide');
            if (this.getHideSidebars()) {
                $(this.getSidebarLayeredNavSelector()).hide();
            }
            var $filters = this.$().find('dl dt.m-ln');
            var self = this;
            $filters.each(function () {
                self._prepareFilterForMobileLayout(this);
            });

            switch (this.getBehavior()) {
                case 'initially-collapsed':
                    this._prepareMobileLayoutByDefaultCollapsed();
                    break;
                case 'initially-expanded':
                    this._prepareMobileLayoutByDefaultExpanded();
                    break;
                case 'accordion':
                default:
                    this._prepareMobileLayoutAsAccordion();
                    break;
            }
            if (this._subTitleExpanded) {
                this.expandSubTitle(0);
            }
            else {
                this.collapseSubTitle(0);
            }
        },
        _prepareMobileLayoutByDefaultExpanded: function() {
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
        _prepareMobileLayoutByDefaultCollapsed: function() {
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
        _prepareWideLayout: function() {
            this._mobileLayout = false;
            this.$().addClass('m-wide');
            if (this.getHideSidebars()) {
                $(this.getSidebarLayeredNavSelector()).show();
            }
            var $filters = this.$().find('dl dt.m-ln');
            var self = this;
            $filters.each(function () {
                self._prepareFilterForWideLayout(this);
                self.expand(this, 0);
            });
            this.expandSubTitle(0);
        },
        _prepareMobileLayoutAsAccordion: function() {
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
        getBehavior: function() {
            if (this._behavior === undefined) {
                this._behavior = this.$().data('behavior');
            }
            return this._behavior;
        },
        getDuration: function() {
            if (this._duration === undefined) {
                this._duration = this.$().data('duration');
                if (this._duration === '') {
                    this._duration = 500;
                }
            }
            return this._duration;
        },
        getHideSidebars: function() {
            if (this._hideColumnFilters === undefined) {
                this._hideColumnFilters = this.$().data('hide-sidebars');
            }
            return this._hideColumnFilters;
        },
        getSidebarLayeredNavSelector: function() {
            return '.col-left .block.block-layered-nav,' +
                '.mb-mana-catalog-leftnav,' +
                '.col-right .block.block-layered-nav,' +
                '.mb-mana-catalog-rightnav';
        },
        getWidths: function() {
            throw 'Abstract';
        },
        _prepareFilterForWideLayout: function(dt) {
        },
        _prepareFilterForMobileLayout: function (dt) {
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

Mana.define('Mana/LayeredNavigation/Top/MenuBlock', ['jquery', 'Mana/LayeredNavigation/TopBlock'],
function($, TopBlock)
{
    return TopBlock.extend('Mana/LayeredNavigation/Top/MenuBlock', {
        _prepareFilterForWideLayout: function(dt) {
            $(dt).next().addClass('hidden');
        },
        _prepareFilterForMobileLayout: function (dt) {
            $(dt).next().removeClass('hidden').width('auto');
        },
        getWidths: function() {
            if (this._widths === undefined) {
                this._widths = {
                    "one-filter-column": this.$().data('one-column')
                };
            }
            return this._widths;
        },
        _calculatePopupWidth: function ($dt, $dd) {
            var maxWidth = $dd.attr('data-max-width');
            var result = $dd.width() > $dt.width() ? $dd.width() : $dt.width();
            return maxWidth ? (result <= maxWidth ? result : maxWidth) : result;
        },
        _subscribeToHtmlEvents: function () {
            var self = this;
            function _mouseOverFilter() {
                self._mouseOverFilter($(this).parent(), $(this), $(this).next());
            }

            function _mouseOutFilter() {
                self._mouseOutFilter($(this).parent(), $(this), $(this).next());
            }

            function _mouseOverOptions() {
                self._mouseOverOptions($(this).parent(), $(this).prev(), $(this));
            }

            function _mouseOutOptions() {
                self._mouseOutOptions($(this).parent(), $(this).prev(), $(this));
            }

            return this
                ._super()
                .on('bind', this, function () {
                    this.$().find('dl dt.m-ln').on('mouseover', _mouseOverFilter);
                    this.$().find('dl dt.m-ln').on('mouseout', _mouseOutFilter);
                    this.$().find('dl dd.m-ln').on('mouseover', _mouseOverOptions);
                    this.$().find('dl dd.m-ln').on('mouseout', _mouseOutOptions);
                })
                .on('unbind', this, function () {
                    this.$().find('dl dt.m-ln').off('mouseover', _mouseOverFilter);
                    this.$().find('dl dt.m-ln').off('mouseout', _mouseOutFilter);
                    this.$().find('dl dd.m-ln').off('mouseover', _mouseOverOptions);
                    this.$().find('dl dd.m-ln').off('mouseout', _mouseOutOptions);
                });
        },
        _mouseOverFilter: function($dl, $dt, $dd) {
            if ($dl.hasClass('m-inline') || this._mobileLayout) {
                return true;
            }

            $dd
              .removeClass('hidden')
              .offset({
                top: $dt.offset().top + $dt.outerHeight(),
                left: $dt.offset().left
              })
              .width(this._calculatePopupWidth($dt, $dd))
              .addClass('m-popup-filter');
            $dt
              .addClass('m-popup-filter');

            return true;
        },
        _mouseOutFilter: function ($dl, $dt, $dd) {
            if ($dl.hasClass('m-inline') || this._mobileLayout) {
                return true;
            }

            $dd
              .removeClass('m-popup-filter')
              .addClass('hidden');
            $dt
              .removeClass('m-popup-filter');

            return true;
        },
        _mouseOverOptions: function ($dl, $dt, $dd) {
            if ($dl.hasClass('m-inline') || this._mobileLayout) {
                return true;
            }

            $dd
              .removeClass('hidden')
              .offset({
                top: $dt.offset().top + $dt.outerHeight(),
                left: $dt.offset().left
              })
              .width(this._calculatePopupWidth($dt, $dd))
              .addClass('m-popup-filter');
            $dt
              .addClass('m-popup-filter');

            return true;
        },
        _mouseOutOptions: function ($dl, $dt, $dd) {
            if ($dl.hasClass('m-inline') || this._mobileLayout) {
                return true;
            }

            $dd
                .removeClass('m-popup-filter')
                .addClass('hidden');
            $dt
                .removeClass('m-popup-filter');

            return true;
        }
    });
});

Mana.define('Mana/LayeredNavigation/Top/HorizontalBlock', ['jquery', 'Mana/LayeredNavigation/TopBlock'],
function($, TopBlock, undefined)
{
    return TopBlock.extend('Mana/LayeredNavigation/Top/HorizontalBlock', {
        _init: function () {
            this._super();
            this._minHeights = {};
        },
        _prepareFilterForWideLayout: function(dt) {
            var minHeight;
            if (minHeight = this._minHeights[$(dt).data('id')]) {
                $(dt).parent().css('min-height', minHeight);
            }
        },
        _prepareFilterForMobileLayout: function (dt) {
            var minHeight;
            if (minHeight = $(dt).parent().css('min-height')) {
                this._minHeights[$(dt).data('id')] = minHeight;
                $(dt).parent().css('min-height', '');
            }
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
        }
    });
});
