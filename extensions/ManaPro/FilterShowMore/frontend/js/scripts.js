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
            return urlTemplate.decodeAttribute(this.$().data('popup-url'));
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
        //endregion

        _openPopup: function() {
            var self = this;
            ajax.get(this.getPopupUrl().replace('__0__', urlTemplate.encodeAttribute(location.href)), function(response) {
                layout.showPopup({
                    content: response,
                    overlay: { opacity: 0.2},
                    popup: { 'class': 'm-showmore-popup-container', blockName: self.getPopupBlockName()},
                    popupBlock: { host: self },
                    fadein: { overlayTime: 0, popupTime: 300, callback: function(popupBlock) {
                        self._popupBlock = popupBlock;
                    }},
                    fadeout: { overlayTime: 0, popupTime: 500, callback: null }
                });
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
                var maxRowCount = rows.attr('data-max-rows');
                var height = 0;
                if (maxRowCount) {
                    rows.children().each(function (index) {
                        if (index < maxRowCount) {
                            height += $(this).outerHeight();
                        }
                    });
                    rows.width(rows.width() + 30).height(height).addClass('m-scrollable-filter');
                }
            });

            this.$().find('button.m-close').on('click', function() { return self._close(); });
            this.$().find('button.m-apply').on('click', function () { return self._apply(); });
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
            itemData.index = $element.data('index');
            if (isSelected) {
                this._selectedItems[itemData.url] = itemData;
            }
            else {
                delete this._selectedItems[itemData.url];
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

//region Obsolete scripts
(function($, undefined) {
	var prefix = 'm-more-less-';
	var _inAjax = false;
	var _states = {};
	var _itemCounts = {};
	var _time = {};
	var _popupUrls = {};
	var _popupTargetUrls = {};
    var _popupClearUrls = {};
    var _popupProgress = false;
    var _popupDebug = false;
    var _lastPopupCode = null;
    var _popupValues = {};
    var _lastPopupValues = null;
    var _popupAction = 'click';

	function _calculateHeights(l, code) {
	    var visible = l.is(':visible');
	    var hiddenElement = l;
	    if (!visible) {
            while (hiddenElement.parent().length && !hiddenElement.parent().is(':visible')) {
                hiddenElement = hiddenElement.parent();
            }
            hiddenElement.show();
	    }
		var heights = {less: 0, more: 0};
		l.children().each(function(index, item) {
            if (
                index < _itemCounts[code] ||
                !l.hasClass('m-reverse') && $(item).hasClass('m-selected-ln-item') ||
                l.hasClass('m-reverse') && !$(item).hasClass('m-selected-ln-item')
            ) {
                heights.less += $(item).outerHeight(true);
            }

			heights.more += $(item).outerHeight(true);
		});
		if (!visible) {
            hiddenElement.hide();
		}
		return heights;
	}
	function apply(code, withTransition) {
		var div = $('#'+prefix+code);
		var l = div.parent().children().first();
        var heights;

        l.addClass('m-expandable-filter');
		if (_states[code]) {
			l.children().each(function(index, item) {
				if (! (
				    index < _itemCounts[code] ||
                    !l.hasClass('m-reverse') && $(item).hasClass('m-selected-ln-item') ||
                    l.hasClass('m-reverse') && !$(item).hasClass('m-selected-ln-item')
				)) {
					$(item).show();
				}
            });

			heights = _calculateHeights(l, code);
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
			l.children().each(function(index, item) {
				if (! (
				    index < _itemCounts[code] ||
                    !l.hasClass('m-reverse') && $(item).hasClass('m-selected-ln-item') ||
                    l.hasClass('m-reverse') && !$(item).hasClass('m-selected-ln-item')
				)) {
                    $(item).hide();
				}
			});
			
			heights = _calculateHeights(l, code);
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
		if (!_inAjax){
			_states[code] = showAll;
		}
		_itemCounts[code] = itemCount;
		_time[code] = time;
		apply(code, false);
	});
    $(document).bind('m-filter-scroll-reset', function (e, code, itemCount) {
        _itemCounts[code] = itemCount;
        var div = $('#' + prefix + code);
        var l = div.parent().children().first();

        l.addClass('m-scrollable-filter');
        var heights = _calculateHeights(l, code);
        l.height(heights.less);
        l.parent().on('m-prepare', function() {
            var heights = _calculateHeights(l, code);
            l.height(heights.less);
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