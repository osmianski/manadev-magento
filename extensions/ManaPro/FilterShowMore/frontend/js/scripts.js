/**
 * @category    Mana
 * @package     ManaPro_FilterShowMore
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.define('Mana/LayeredNavigation/ShowMore/PopupAction', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/UrlTemplate',
    'singleton:Mana/Core/Json', 'singleton:Mana/Core/Ajax', 'singleton:Mana/Core/Layout', 'singleton:Mana/Core'],
function ($, Block, urlTemplate, json, ajax, layout, core, undefined)
{
    return Block.extend('Mana/LayeredNavigation/ShowMore/PopupAction', {
        _subscribeToHtmlEvents: function () {
            var self = this;

            function _openPopup() {
                self._openPopup();
                return false;
            }

            return this
                ._super()
                .on('bind', this, function () {
                    this.$().find('a.m-show-more-popup-action').on('click', _openPopup);
                })
                .on('unbind', this, function () {
                    this.$().find('a.m-show-more-popup-action').off('click', _openPopup);
                });
        },

        //region Data Attributes
        getPopupUrl: function () {
            return decodeURIComponent(urlTemplate.decodeAttribute(this.$().data('popup-url')));
        },
        getTargetUrl: function () {
            if (this._targetUrl === undefined) {
                this._targetUrl = urlTemplate.decodeAttribute(this.$().data('target-url'));
            }
            return this._targetUrl;
        },
        getClearUrl: function () {
            if (this._clearUrl === undefined) {
                this._clearUrl = urlTemplate.decodeAttribute(this.$().data('clear-url'));
            }
            return this._clearUrl;
        },
        getSelectedItems: function() {
            return json.decodeAttribute(this.$().data('selected-items'));
        },
        getPopupBlockName: function() {
            if (this._popupBlockName === undefined) {
                this._popupBlockName = this.$().data('popup-block');
            }
            return this._popupBlockName;
        },
        getSeparator: function() {
            if (this._separator === undefined) {
                this._separator = this.$().data('separator');
            }
            return this._separator;
        },
        getColumnCount: function () {
            return this.$().data('column-count');
        },
        getRowCount: function () {
            return this.$().data('row-count');
        },
        //endregion
        rearrangeItems: function($popup) {
            var self = this;
            var $unorganizedColumns = $popup.find('.m-columns');
            var $items = $popup.find('.m-columns li');
            var columnClass = $unorganizedColumns.attr('class');
            var horizontalColors = $unorganizedColumns.hasClass('m-filter-colors') && $unorganizedColumns.hasClass('horizontal');
            var $rows = $popup.find('.m-rows');
            var $rowsToBeRemoved = $rows.children('li');
            if ($items.length) {
                var scrollBarWidth = 30, reservedMargin = 20;
                var popupWidth = $(window).width() - ($popup.outerWidth() - $rows.outerWidth())
                    - scrollBarWidth - reservedMargin;
                var popupHeight = $(window).height() - ($popup.outerHeight() - $rows.outerHeight()) - reservedMargin;
                var columnWidth = horizontalColors ? $unorganizedColumns.outerWidth() : $items.first().outerWidth();
                var columnCount = Math.floor(popupWidth / columnWidth);
                var height = 0;

                if (columnCount < 1) {
                    columnCount = 1;
                    if (horizontalColors) {
                        columnWidth = popupWidth - (columnWidth - $unorganizedColumns.width());
                        columnWidth = Math.floor(columnWidth / $items.first().outerWidth()) * $items.first().outerWidth() + 4;
                        $unorganizedColumns.width(columnWidth);
                    }
                    else {
                        $items.width(popupWidth - (columnWidth - $items.first().width()));
                    }
                }
                if (columnCount > self.getColumnCount()) {
                    columnCount = self.getColumnCount();
                }

                if (horizontalColors) {
                    var scrollHeight, rowIndex = 0, top, rowTop;
                    $items.each(function () {
                        var $item = $(this);
                        if (top === undefined) {
                            rowTop = top = $item.position().top;
                        }
                        else if (top < $item.position().top) {
                            rowIndex++;
                            top = $item.position().top;
                        }
                        height = top - rowTop + $item.outerHeight();
                        if (scrollHeight === undefined && (height > popupHeight || rowIndex + 1 >= self.getRowCount())) {
                            scrollHeight = top - rowTop;
                        }
                    });
                    if (scrollHeight !== undefined) {
                        $rows.attr('data-max-height', scrollHeight);
                    }
                }
                else {
                    var $columns, $row, scrollRows, $hiddenRow, index = 0;

                    $items.each(function () {
                        var item = this, $item = $(item), visible = !$item.hasClass('m-no-match');
                        if (visible) {
                            var columnIndex = index % columnCount;
                            var rowIndex = Math.floor(index / columnCount);
                            if (columnIndex === 0) {
                                $row = $('<li><ol class="' + columnClass + '"></ol></li>');
                                $rows.append($row);
                            }
                            $columns = $row.children().first();
                            $columns.append(item);
                            if (columnIndex == columnCount - 1) {
                                height += $row.outerHeight();
                                if (scrollRows === undefined && (height > popupHeight || rowIndex + 1 >= self.getRowCount())) {
                                    scrollRows = rowIndex;
                                }
                            }
                            index++;
                        }
                        else {
                            if (!$hiddenRow) {
                                $hiddenRow = $('<li><ol class="' + columnClass + '"></ol></li>').hide();
                                $rows.append($hiddenRow);
                            }
                            $columns = $hiddenRow.children().first();
                            $columns.append(item);
                        }
                    });
                    if (scrollRows !== undefined) {
                        $rows.attr('data-max-rows', scrollRows);
                    }
                    $rowsToBeRemoved.remove();
                }
            }
        },

        _openPopup: function() {
            var self = this;

            var url = this.getPopupUrl()
                .replace('__0__', urlTemplate.encodeAttribute(location.href));
            ajax.get(url, function(response) {
                var options = {
                    content: response,
                    overlay: { opacity: 0.2},
                    popup: { 'class': 'm-showmore-popup-container', blockName: self.getPopupBlockName()},
                    popupBlock: { host: self },
                    fadein: { overlayTime: 0, popupTime: 300, callback: function(popupBlock) {
                        self._popupBlock = popupBlock;
                    }},
                    fadeout: { overlayTime: 0, popupTime: 500, callback: null }
                };
                var $popup = layout.preparePopup(options);

                $popup.show();
                self.rearrangeItems($popup);
                $popup.hide();

                layout.showPopup(options);
            });
        },
        close: function() {
            layout.hidePopup();
        },
        apply: function(items) {
            layout.hidePopup();

            if (core.count(items)) {
                var sortedItems = [];
                $.each(items, function(index, value) {
                    sortedItems.push(value);
                });
                sortedItems.sort(function(a, b) {
                    if (a.position < b.position) return -1;
                    if (a.position > b.position) return 1;

                    if (parseInt(a.id) < parseInt(b.id)) return -1;
                    if (parseInt(a.id) > parseInt(b.id)) return 1;

                    if (a.index < b.index) return -1;
                    if (a.index > b.index) return 1;

                    return 0;
                });
                if (sortedItems.length == 1 && sortedItems[0].full_url) {
                    setLocation(sortedItems[0].full_url);
                }
                else {
                    var param = '';
                    var prefix = '';
                    var separator = this.getSeparator();
                    $.each(sortedItems, function (index, value) {
                        if (value.prefix) {
                            prefix = value.prefix;
                        }
                        if (param) {
                            param += separator;
                        }
                        param += value.url;
                    });
                    setLocation(this.getTargetUrl().replace('__0__', prefix + param));
                }
            }
            else {
                setLocation(this.getClearUrl());
            }
        }
    });
});

Mana.define('Mana/LayeredNavigation/Popup', ['jquery', 'Mana/Core/Block', 'singleton:Mana/Core/Json'],
function ($, Block, json) {
    return Block.extend('Mana/LayeredNavigation/Popup', {
        prepare: function(options) {
            var self = this;
            this._host = options.host;
            this._selectedItems = this._host.getSelectedItems();

            this.$().find('.m-rows').each(function () {
                var rows = $(this);
                var maxRowCount, maxRowHeight;
                var height = 0;
                if (maxRowCount = rows.attr('data-max-rows')) {
                    rows.children().each(function (index) {
                        if (index < maxRowCount) {
                            height += $(this).outerHeight();
                        }
                    });
                    rows.width(rows.width() + 30).height(height).addClass('m-scrollable-filter');
                }
                else if (maxRowHeight = rows.attr('data-max-height')) {
                    rows.height(maxRowHeight).addClass('m-scrollable-filter');
                }
            });

            this.$().find('button.m-close').on('click', function() { return self._close(); });
            this.$().find('button.m-apply').on('click', function () { return self._apply(); });
        },
        rearrangeItems: function() {
            return this._host.rearrangeItems(this.$());
        },
        _close: function() {
            this._host.close();
            return false;
        },
        _apply: function() {
            this._host.apply(this._selectedItems);
            return false;
        },
        _toggleItem: function(element, isSelected) {
            var $element = $(element);
            if ($element.data('is-reverse')) {
                isSelected = !isSelected;
            }
            var itemData = json.decodeAttribute($element.data('item'));
            if (itemData) {
                itemData.index = $element.data('index');
                if (isSelected) {
                    this._selectedItems[itemData.url] = itemData;
                }
                else {
                    delete this._selectedItems[itemData.url];
                }
            }
            return false;
        },
        _setItem: function (element) {
            var $element = $(element);
            var itemData = json.decodeAttribute($element.data('item'));
            itemData.index = $element.data('index');
            var key = itemData.url;
            this._selectedItems = {key: itemData };
            return false;
        }
    });
});

Mana.define('Mana/LayeredNavigation/Popup/CssCheckbox', ['jquery', 'Mana/LayeredNavigation/Popup'],
function ($, Popup) {
    return Popup.extend('Mana/LayeredNavigation/Popup/CssCheckbox', {
        prepare: function(options) {
            this._super(options);
            var self = this;
            this.$().find('a.m-checkbox-checked, a.m-checkbox-unchecked').on('click', function() {
                return self._toggle(this);
            });
        },
        _toggle: function (element) {
            var $element = $(element);
            $element.parent().toggleClass('m-selected-ln-item');
            $element
                .toggleClass('m-checkbox-checked')
                .toggleClass('m-checkbox-unchecked');
            return this._toggleItem(element, $element.parent().hasClass('m-selected-ln-item'));
        }

    });
});

Mana.define('Mana/LayeredNavigation/Popup/Checkbox', ['jquery', 'Mana/LayeredNavigation/Popup'],
function ($, Popup) {
    return Popup.extend('Mana/LayeredNavigation/Popup/Checkbox', {
        prepare: function(options) {
            this._super(options);
            var self = this;
            this.$().find('ol.m-columns > li > input').on('click', function () {
                return self._toggle(this);
            });
        },
        _toggle: function (element) {
            var $element = $(element);
            $element.parent().toggleClass('m-selected-ln-item');
            this._toggleItem(element, $element.parent().hasClass('m-selected-ln-item'));
        }

    });
});

Mana.define('Mana/LayeredNavigation/Popup/Standard', ['jquery', 'Mana/LayeredNavigation/Popup'],
function ($, Popup) {
    return Popup.extend('Mana/LayeredNavigation/Popup/Standard', {
        prepare: function(options) {
            this._super(options);
            var self = this;
            this.$().find('ol.m-columns > li > a').on('click', function() {
                return self._toggle(this);
            });
        },
        _toggle: function (element) {
            this._setItem(element);
            return this._apply();
        }

    });
});

Mana.define('Mana/LayeredNavigation/Popup/ColorOne', ['jquery', 'Mana/LayeredNavigation/Popup'],
function ($, Popup) {
    return Popup.extend('Mana/LayeredNavigation/Popup/ColorOne', {
        prepare: function(options) {
            this._super(options);
            var self = this;
            this.$().find('ol.m-columns > li > a').on('click', function() {
                return self._toggle(this);
            });
        },
        _toggle: function (element) {
            this._setItem(element);
            return this._apply();
        }

    });
});
Mana.define('Mana/LayeredNavigation/Popup/List', ['jquery', 'Mana/LayeredNavigation/Popup'],
function ($, Popup) {
    return Popup.extend('Mana/LayeredNavigation/Popup/List', {
        prepare: function(options) {
            this._super(options);
            var self = this;
            this.$().find('ol.m-columns > li > a').on('click', function () {
                return self._toggle(this);
            });
        },
        _toggle: function (element) {
            var $element = $(element);
            $element.parent().toggleClass('m-selected-ln-item');
            $element.children().toggleClass('m-selected-filter-item');
            return this._toggleItem(element, $element.parent().hasClass('m-selected-ln-item'));
        }

    });
});

Mana.define('Mana/LayeredNavigation/Popup/Color', ['jquery', 'Mana/LayeredNavigation/Popup'],
function ($, Popup) {
    return Popup.extend('Mana/LayeredNavigation/Popup/Color', {
        prepare: function(options) {
            this._super(options);
            var self = this;
            this.$().find('ol.m-columns > li > a').on('click', function () {
                return self._toggle(this);
            });
        },
        _toggle: function (element) {
            var $element = $(element);
            $element.parent().toggleClass('m-selected-ln-item');
            $element.find('div').toggleClass('selected');
            return this._toggleItem(element, $element.parent().hasClass('m-selected-ln-item'));
        }

    });
});

Mana.define('Mana/LayeredNavigation/Popup/ColorWithLabel', ['jquery', 'Mana/LayeredNavigation/Popup'],
function ($, Popup) {
    return Popup.extend('Mana/LayeredNavigation/Popup/ColorWithLabel', {
        prepare: function (options) {
            this._super(options);
            var self = this;
            this.$().find('ol.m-columns > li > a').on('click', function () {
                return self._toggle(this);
            });
        },
        _toggle: function (element) {
            var $element = $(element);
            $element.parent().toggleClass('m-selected-ln-item');
            $element.find('div').toggleClass('selected');
            return this._toggleItem(element, $element.parent().hasClass('m-selected-ln-item'));
        }

    });
});

Mana.define('Mana/LayeredNavigation/Popup/Radio', ['jquery', 'Mana/LayeredNavigation/Popup'],
function ($, Popup) {
    return Popup.extend('Mana/LayeredNavigation/Popup/Radio', {
        prepare: function(options) {
            this._super(options);
            var self = this;
            this.$().find('ol.m-columns > li > input').on('click', function () {
                return self._toggle(this);
            });
        },
        _toggle: function (element) {
            this._setItem(element);
            this._apply();
        }

    });
});

Mana.define('Mana/LayeredNavigation/OptionSearch', ['jquery', 'Mana/Core/Block'],
function ($, Block, undefined) {
    return Block.extend('Mana/LayeredNavigation/OptionSearch', {
        _subscribeToHtmlEvents: function () {
            var self = this;

            function _search() {
                self._search();
            }

            function _focus() {
                self._focus();
            }

            function _blur() {
                self._blur();
            }

            return this
                ._super()
                .on('bind', this, function () {
                    this.$input().on('focus', _focus);
                    this.$input().on('blur', _blur);
                    this.$input().on('keyup', _search);
                    this.$input().on('change', _search);
                    if (this._needle !== undefined) {
                        this.$input().val(this._needle);
                        this._focus();
                        this._blur();
                        this._search();
                    }
                })
                .on('unbind', this, function () {
                    this.$input().off('focus', _focus);
                    this.$input().off('blur', _blur);
                    this.$input().off('keyup', _search);
                    this.$input().off('change', _search);
                });
        },
        $input: function() {
            return this.$().find('input');
        },
        $list: function() {
            return this.$().parent().children(':not(.m-option-search)').first();
        },
        $items: function() {
            return this.$list().children();
        },
        _search: function() {
            var needle;
            if (this.$input().val() != this.$input().attr("title")) {
                needle = this.$input().val().toLowerCase();
                var $list = this.$list();
                this.$items().each(function() {
                    var $item = $(this);
                    var haystack = $list.is('.m-filter-colors.vertical,.m-columns.m-filter-colors.horizontal') ? $item.find('a > div').attr('title') :
                        ($list.is('.m-filter-colors.horizontal') ? $item.children('div').attr('title') :
                        $item.text());
                    haystack = haystack.toLowerCase();
                    if (haystack.indexOf(needle) != -1) {
                        $item.removeClass('m-no-match');
                    }
                    else {
                        $item.addClass('m-no-match');
                    }
                });
            }
            else {
                needle = '';
                this.$items().removeClass('m-no-match');
            }
            this._needle = needle;
            this._afterSearch();
        },
        _afterSearch: function() {
            this.$().parent().trigger('m-prepare');
        },
        _focus: function() {
            var $input = this.$input();
            if ($input.val() == $input.attr("title")) {
                $input.val("");
            }
            $input.removeClass('m-empty');
        },
        _blur: function() {
            var $input = this.$input();
            if ($input.val() == "") {
                $input.val($input.attr("title")).addClass('m-empty');
            }
            else {
                $input.removeClass('m-empty');
            }
        }
    });
});

Mana.define('Mana/LayeredNavigation/OptionSearch/Popup', ['jquery', 'Mana/LayeredNavigation/OptionSearch'],
function ($, OptionSearch) {
    return OptionSearch.extend('Mana/LayeredNavigation/OptionSearch/Popup', {
        $list: function () {
            return this.$().parent().children(':not(.m-option-search)').first().find('.m-columns');
        },
        _afterSearch: function () {
            this._super();
            this.getParent().rearrangeItems();
        }
    });
});

//region old style show more/show less and scroll bar scripts
(function($, undefined) {
	var prefix = 'm-more-less-';
	var _inAjax = false;
	var _states = {};
	var _itemCounts = {};
	var _time = {};

    function _calculateHeights(l, code) {
        if (l.is('.m-filter-colors.horizontal')) {
            return _calculateHorizontalColorHeights(l, code);
        }
        else {
            return _calculateOtherHeights(l, code);
        }
    }

	function _calculateOtherHeights(l, code) {
	    var visible = l.is(':visible');
	    var hiddenElement = l;
	    var hiddenDisplayStyle;

	    if (!visible) {
            while (hiddenElement.parent().length && !hiddenElement.parent().is(':visible')) {
                hiddenElement = hiddenElement.parent();
            }
            hiddenDisplayStyle = hiddenElement.length ? hiddenElement[0].style.display : undefined;
            hiddenElement.show();
	    }
		var heights = {less: 0, more: 0, count: 0};
		l.children(':not(.m-no-match)').each(function(index, item) {
            if (
                index < _itemCounts[code] ||
                !l.hasClass('m-reverse') && $(item).hasClass('m-selected-ln-item') ||
                l.hasClass('m-reverse') && !$(item).hasClass('m-selected-ln-item')
            ) {
                heights.less += $(item).outerHeight(true);
            }

			heights.more += $(item).outerHeight(true);
			heights.count++;
		});
		if (!visible && hiddenElement.length) {
            hiddenElement[0].style.display = hiddenDisplayStyle;
		}
		return heights;
	}
	function _calculateHorizontalColorHeights(l, code) {
	    var visible = l.is(':visible');
	    var hiddenElement = l;
	    if (!visible) {
            while (hiddenElement.parent().length && !hiddenElement.parent().is(':visible')) {
                hiddenElement = hiddenElement.parent();
            }
            hiddenElement.show();
	    }
		var heights = {less: 0, more: 0, count: 0}, firstTop;
		l.children(':not(.m-no-match)').each(function(index, item) {
		    var $item = $(item).children().first();
		    if (firstTop === undefined) {
                firstTop = $item.position().top;
		    }
		    var bottom = $item.position().top - firstTop + $item.outerHeight(true);
            if (
                index < _itemCounts[code] ||
                !l.hasClass('m-reverse') && $(item).hasClass('m-selected-ln-item') ||
                l.hasClass('m-reverse') && !$(item).hasClass('m-selected-ln-item')
            ) {
                heights.less = bottom;
            }

			heights.more = bottom;
            heights.count++;
        });
		if (!visible) {
            hiddenElement.hide();
		}
		return heights;
	}
	function apply(code, withTransition) {
		var div = $('#'+prefix+code);
		var l = div.parent().children(':not(.m-option-search)').first();
        var heights;

//        l.addClass('m-expandable-filter');
//        if (l.is('.m-filter-colors.horizontal')) {
            heights = _calculateHeights(l, code);
            if (heights.count <= _itemCounts[code]) {
                l.removeClass('m-expandable-filter');
                div.hide();
                if (withTransition) {
                    l.animate({height: heights.more + 'px'}, _time[code]);
                }
                else {
                    l.height(heights.more);
                }
            }
            else {
                l.addClass('m-expandable-filter');
                div.show();
                if (_states[code]) {
                    if (withTransition) {
                        l.animate({height: heights.more+'px'}, _time[code]);
                    }
                    else {
                        l.height(heights.more);
                    }
                    div.find('.m-show-less-action').show();
                    div.find('.m-show-more-action').hide();
                }
                else {
                    if (withTransition) {
                        l.animate({height: heights.less+'px'}, _time[code]);
                    }
                    else {
                        l.height(heights.less);
                    }
                    div.find('.m-show-less-action').hide();
                    div.find('.m-show-more-action').show();
                }
            }
//        }
//        else {
//            if (_states[code]) {
//                l.children().each(function(index, item) {
//                    if (! (
//                        index < _itemCounts[code] ||
//                        !l.hasClass('m-reverse') && $(item).hasClass('m-selected-ln-item') ||
//                        l.hasClass('m-reverse') && !$(item).hasClass('m-selected-ln-item')
//                    )) {
//                        $(item).show();
//                    }
//                });
//
//                heights = _calculateHeights(l, code);
//                if (withTransition) {
//                    l.animate({height: heights.more+'px'}, _time[code]);
//                }
//                else {
//                    l.height(heights.more);
//                }
//                div.find('.m-show-less-action').show();
//                div.find('.m-show-more-action').hide();
//            }
//            else {
//                l.children().each(function(index, item) {
//                    if (! (
//                        index < _itemCounts[code] ||
//                        !l.hasClass('m-reverse') && $(item).hasClass('m-selected-ln-item') ||
//                        l.hasClass('m-reverse') && !$(item).hasClass('m-selected-ln-item')
//                    )) {
//                        $(item).hide();
//                    }
//                });
//
//                heights = _calculateHeights(l, code);
//                if (withTransition) {
//                    l.animate({height: heights.less+'px'}, _time[code]);
//                }
//                else {
//                    l.height(heights.less);
//                }
//                div.find('.m-show-less-action').hide();
//                div.find('.m-show-more-action').show();
//            }
//        }
	}

    function getFilterCode(el) {
        var code = $(el).parent()[0].id;
        if (!code.match("^" + prefix) == prefix) {
            throw 'Unexpected show more/show less id';
        }
        return code.substring(prefix.length);
    }
	function clickHandler() {
		var code = getFilterCode(this);
		_states[code] = !_states[code];
		apply(code, true);
		return false;
	}

    $(document).bind('m-show-more-reset', function(e, code, itemCount, showAll, time) {
        var div = $('#' + prefix + code);
        if (!_inAjax){
			_states[code] = showAll;
		}
		_itemCounts[code] = itemCount;
		_time[code] = time;
		apply(code, false);
        div.parent().on('m-prepare', function() {
    		apply(code, false);
        });
	});
	function _initFilterScrollBar(l, code) {
        var heights = _calculateHeights(l, code);
        if (heights.count > _itemCounts[code]) {
            l.addClass('m-scrollable-filter');
            l.parent().addClass('m-scrollable-filter-container');
            l.height(heights.less);
        }
        else {
            l.removeClass('m-scrollable-filter');
            l.parent().removeClass('m-scrollable-filter-container');
            l.height('auto');
        }
    }
    $(document).bind('m-filter-scroll-reset', function (e, code, itemCount) {
        _itemCounts[code] = itemCount;
        var div = $('#' + prefix + code);
        var l = div.parent().children(':not(.m-option-search)').first();

        _initFilterScrollBar(l, code);
        l.parent().on('m-prepare', function() {
            _initFilterScrollBar(l, code);
        });
    });
    $(document).bind('m-ajax-before', function(e, selectors) {
		_inAjax = true;
	});
	$(document).bind('m-ajax-after', function(e, selectors) {
		for (var code in _states) {
			apply(code, false);
		}
		_inAjax = false;
	});
	$('a.m-show-less-action').live('click', clickHandler);
	$('a.m-show-more-action').live('click', clickHandler);

})(jQuery);
//endregion