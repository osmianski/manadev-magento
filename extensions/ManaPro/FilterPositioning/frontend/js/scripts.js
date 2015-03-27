/**
 * @category    Mana
 * @package     ManaPro_FilterPositioning
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */


; // make JS merging easier

Mana.define('Mana/LayeredNavigation/TopBlock', ['jquery', 'Mana/Core/Block'],
function($, Block, undefined)
{
    return Block.extend('Mana/LayeredNavigation/TopBlock', {
        _init: function () {
            this._super();
            this._expandCollapseStates = {};
            this._accordionExpandedId = undefined;
            this._mobileLayout = false;
            this._subTitleExpanded = false;
            this._replacedTitles = [];
        },
        _subscribeToHtmlEvents: function () {
            var self = this;
            function _expandCollapse(e) {
                self.expandCollapse(this, e);
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
                    this._replacedTitles = [];
                    if (this.getSubtitleBehavior() == 'expand') {
                        this._subTitleExpanded = true;
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
                .on('load', this, function() {
                    if (!this.$().hasClass("one-filter-column")) {
                        this._prepareWideLayout();
                    }
                })
                .on('resize', this, this.resize);
        },
        resize: function() {
            var self = this;
            var widthClassFound = false;
            var wasOneColumn = this.$().hasClass("one-filter-column");
            $.each(this.getWidths(), function(cls, width) {
                var contentWidth = cls == "one-filter-column" ? $('body').width() : self.$().width();

                if (width && !widthClassFound && contentWidth < width) {
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
        expandCollapse: function(dt, e) {
            if (this._mobileLayout && !$(e.target).parents('.m-filterclear').length) {
                var id = $(dt).data('id');
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
            this._replacedTitles = [];
            this.$().find('.block-subtitle').each(function() {
                var $text = $(this).find('span');
                self._replacedTitles.push({
                    element: this,
                    title: $(this).html(),
                    hidden: $(this).hasClass('hidden')
                });
                $(this).removeClass('hidden');
                $text.html(self.getMobileTitle());
            });
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
            $.each(this._replacedTitles, function(index, replacement) {
                var $element = $(replacement.element);
                var $text = $element.find('span');
                $text.html(replacement.title);
                if (replacement.hidden) {
                    $element.addClass('hidden');
                }
            });
            this._replacedTitles = [];
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
        getSubtitleBehavior: function() {
            if (this._subtitleBehavior === undefined) {
                this._subtitleBehavior = this.$().data('subtitle-behavior');
            }
            return this._subtitleBehavior;
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
        getMobileTitle: function() {
            if (this._mobileTitle === undefined) {
                this._mobileTitle = this.$().data('title');
            }
            return this._mobileTitle;
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
                '.mb-mana-catalogsearch-leftnav,' +
                '.col-right .block.block-layered-nav,' +
                '.mb-mana-catalog-rightnav' +
                '.mb-mana-catalogsearch-leftnav';
        },
        getWidths: function() {
            throw 'Abstract';
        },
        _prepareFilterForWideLayout: function(dt) {
            var $dd = $(dt).next();
            $dd.trigger('m-prepare');
        },
        _prepareFilterForMobileLayout: function (dt) {
            var $dd = $(dt).next();
            $dd.trigger('m-prepare');
        },
        expand: function (element, duration) {
            $(element).removeClass('m-collapsed').addClass('m-expanded');
            //this._fixSliderWidth(element);
            $(element).next().trigger('m-prepare');
            $(element).next().slideDown(duration);
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
                $(this).next().trigger('m-prepare');
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
function($, TopBlock, undefined)
{
    return TopBlock.extend('Mana/LayeredNavigation/Top/MenuBlock', {
        _prepareFilterForWideLayout: function(dt) {
            var $dl = $(dt).parent();
            if ($dl.hasClass('m-removed-inline')) {
                $dl.addClass('m-inline').removeClass('m-removed-inline');
            }
            if (!$dl.hasClass('m-inline')) {
                $(dt).next().addClass('hidden');
            }
            this._super();
        },
        _prepareFilterForMobileLayout: function (dt) {
            var $dl = $(dt).parent();
            if ($dl.hasClass('m-inline')) {
                $dl.addClass('m-removed-inline').removeClass('m-inline');
            }
            $(dt).next().removeClass('hidden').width('auto');
            this._super();
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
            var minWidth = this.getMinWidth();
            var maxWidth = this.getMaxWidth();
            var result = $dd.width() > $dt.width() ? $dd.width() : $dt.width();
            result = maxWidth ? (result <= maxWidth ? result : maxWidth) : result;
            result = minWidth ? (result >= minWidth ? result : minWidth) : result;
            return result;
        },
        _subscribeToHtmlEvents: function () {
            var self = this;
            function _mouseEnterFilter() {
                self._mouseEnterFilter($(this).parent(), $(this), $(this).next());
            }

            function _mouseLeaveFilter() {
                self._mouseLeaveFilter($(this).parent(), $(this), $(this).next());
            }

            function _mouseEnterOptions() {
                self._mouseEnterOptions($(this).parent(), $(this).prev(), $(this));
            }

            function _mouseLeaveOptions() {
                self._mouseLeaveOptions($(this).parent(), $(this).prev(), $(this));
            }

            return this
                ._super()
                .on('bind', this, function () {
                    this.$().find('dl dt.m-ln').on('mouseenter', _mouseEnterFilter);
                    this.$().find('dl dt.m-ln').on('mouseleave', _mouseLeaveFilter);
                    this.$().find('dl dd.m-ln').on('mouseenter', _mouseEnterOptions);
                    this.$().find('dl dd.m-ln').on('mouseleave', _mouseLeaveOptions);
                })
                .on('unbind', this, function () {
                    this.$().find('dl dt.m-ln').off('mouseenter', _mouseEnterFilter);
                    this.$().find('dl dt.m-ln').off('mouseleave', _mouseLeaveFilter);
                    this.$().find('dl dd.m-ln').off('mouseenter', _mouseEnterOptions);
                    this.$().find('dl dd.m-ln').off('mouseleave', _mouseLeaveOptions);
                });
        },
        _mouseEnterFilter: function($dl, $dt, $dd) {
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

            $dd.trigger('m-prepare');
            return true;
        },
        _mouseLeaveFilter: function ($dl, $dt, $dd) {
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
        _mouseEnterOptions: function ($dl, $dt, $dd) {
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
        _mouseLeaveOptions: function ($dl, $dt, $dd) {
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
        getMinWidth: function () {
            if (this._minWidth === undefined) {
                this._minWidth = this.$().data('min-width');
            }
            return this._minWidth;
        },
        getMaxWidth: function () {
            if (this._maxWidth === undefined) {
                this._maxWidth = this.$().data('max-width');
            }
            return this._maxWidth;
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
            this._super();
        },
        _prepareFilterForMobileLayout: function (dt) {
            var minHeight;
            if (minHeight = $(dt).parent().css('min-height')) {
                this._minHeights[$(dt).data('id')] = minHeight;
                $(dt).parent().css('min-height', '');
            }
            this._super();
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
