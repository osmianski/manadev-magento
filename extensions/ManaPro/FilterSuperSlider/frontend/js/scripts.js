/**
 * @category    Mana
 * @package     ManaPro_FilterSuperSlider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
;var ManaPro = ManaPro || {};
var _mana_oldResizehandler = {};
var _mana_sliderTimers = {};
ManaPro.filterSuperSlider = function(id, o) {
    var _changing = false;
    var $_from = jQuery('#' + id + '-applied input.m-slider.m-from');
    var $_to = jQuery('#' + id + '-applied input.m-slider.m-to');
    var _value = [
        _parse($_from.val()),
        _parse($_to.val())
    ];

    function _parse(stringValue) {
        if (typeof stringValue != 'undefined') {
            stringValue = stringValue.toString().replace(/,([^,]*)$/, '.$1');
        }
        return parseFloat(stringValue);
    }

    function _round(value) {
        if (o.existingValues.length) {
            var distance = 0;
            var found = -1;
            o.existingValues.each(function (item, index) {
                if (found == -1 || distance >= Math.abs(item - value)) {
                    found = index;
                    distance = Math.abs(item - value);
                }
            });
            //console.log(value + ' => ' + o.existingValues[found]);
            value = _parse(o.existingValues[found]);
        }
        if (o.formatThreshold && value >= o.formatThreshold) {
            return o.decimalDigits2 ? _parse(value.toFixed(o.decimalDigits2)) : value.round();
        }
        else {
            return o.decimalDigits ? _parse(value.toFixed(o.decimalDigits)) : value.round();
        }
    }
    function _format(value) {
        if (o.formatThreshold && value >= o.formatThreshold) {
            value = _round(value) / o.formatThreshold;
            value = o.decimalDigits2 ? value.toFixed(o.decimalDigits2) : value.round();
            return o.numberFormat2.replace('0', _formatNumber(value, o.decimalDigits2) + '');
        }
        else {
            return o.numberFormat.replace('0', _formatNumber(_round(value), o.decimalDigits) + '');
        }

    }

    function _formatNumber(value, decPlaces) {
        var thouSeparator = o.thousandSeparator ? o.groupSymbol : '';
        var decSeparator = o.decimalSymbol;
        var n = value;
        var
            sign = n < 0 ? "-" : "",
            i = parseInt(n = Math.abs(+n || 0).toFixed(decPlaces)) + "",
            j = (j = i.length) > 3 ? j % 3 : 0;
        return sign
            + (j ? i.substr(0, j)
            + thouSeparator : "")
            + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thouSeparator)
            + (decPlaces ? decSeparator + Math.abs(n - i).toFixed(decPlaces).slice(2) : "");
    }
    function _change(value, undefined) {
        if (_changing) {
            return;
        }
        _changing = true;

        if (value === undefined) {
            value = [
                _parse($_from.val()),
                _parse($_to.val())
            ];
            if (isNaN(value[0]) || isNaN(value[1])) {
                _changing = false;
                return;
            }

            if (value[0] == _value[0] && value[1] == _value[1]) {
                _changing = false;
                return;
            }

            else if (value[0] > value[1]) {
                var t = value[0];
                value[0] = value[1];
                value[1] = t;
            }
        }
        if (value[0] <= o.rangeFrom && value[1] >= o.rangeTo) {
            window.setLocation(jQuery.base64_decode(o.clearUrl));
        }
        else {
            var formattedValue = [_round(value[0]), _round(value[1])];
            window.setLocation(jQuery.base64_decode(o.url).replace("__0__", formattedValue[0]).replace("__1__", formattedValue[1]));
        }
        _changing = false;
    }
	var s = new Control.PriceSlider([id + '-from', id + '-to'], id + '-track', {
		spans: [id + '-span'], 
		restricted: true,
		range: $R(o.rangeFrom, o.rangeTo),
		sliderValue: [o.appliedFrom, o.appliedTo]
	});
	
	s.options.onSlide = function(value) {
	    if (o.manualEntry) {
	        jQuery('#'+id+'-applied input.m-slider.m-from').val(_round(value[0]));
            jQuery('#'+id+'-applied input.m-slider.m-to').val(_round(value[1]));
	    }
	    else {
            var formattedValue = [ _format(value[0]), _format(value[1])];
            $(id + '-applied').update(o.appliedFormat.replace("__0__", formattedValue[0]).replace("__1__", formattedValue[1]));
        }
	};
	s.options.onChange = _change;

    var _timer = null;
    $_from
        .change(function (event) {
            if (!_timer) {
                _timer = setTimeout(function () {
                    clearTimeout(_timer);
                    _timer = null;
                    _change();
                }, 100);
            }
        })
        .keypress(function (e) {
            if (e.which == 13) {
                if (!_timer) {
                    _timer = setTimeout(function () {
                        clearTimeout(_timer);
                        _timer = null;
                        _change();
                    }, 100);
                }
            }
            else if (e.which == 27) {
                $_from.val(_value[0]);
            }
        })
        .focus(function () {
            var self = this;
            var focusing = setTimeout(function () {
                clearTimeout(focusing);
                focusing = null;
                $(self).select();
            }, 100);
        });
    $_to
        .change(function () {
            if (_timer) {
                clearTimeout(_timer);
                _timer = null;
            }
            _change();
        })
        .keypress(function (e) {
            if (e.which == 13) {
                if (_timer) {
                    clearTimeout(_timer);
                    _timer = null;
                }
                _change();
            }
            else if (e.which == 27) {
                $_to.val(_value[1]);
            }
        })
        .focus(function () {
            if (_timer) {
                clearTimeout(_timer);
                _timer = null;
            }
            var self = this;
            var focusing = setTimeout(function () {
                clearTimeout(focusing);
                focusing = null;
                $(self).select();
            }, 100);
        })
        .blur(function () {
            if (_timer) {
                clearTimeout(_timer);
                _timer = null;
            }
            _change();
        });

    function _resizeSpanAndHandles(forceResize) {
        var checkFrequency = 100, stabilityPeriod = 500;
        var checkingForStability = false, currentlyStableFor = 0;
        if (forceResize === true) {
            s.resize();
        }
        if (!_mana_sliderTimers[id]) {
            _mana_sliderTimers[id] = setInterval(function () {
                if (s.needsResize()) {
                    //console.log(id + ': resize');
                    s.resize();
                    checkingForStability = false;
                }
                else if (!checkingForStability){
                    checkingForStability = true;
                    currentlyStableFor = 0;
                    //console.log(id + ': checking for stability ' + currentlyStableFor);
                }
                else if (currentlyStableFor >= stabilityPeriod){
                    //console.log('stable');
                    clearInterval(_mana_sliderTimers[id]);
                    _mana_sliderTimers[id] = null;
                }
                else {
                    currentlyStableFor += checkFrequency;
                    //console.log('checking for stability ' + currentlyStableFor);
                }

            }, checkFrequency);
        }
    }
    if (_mana_oldResizehandler[id]) {
        jQuery(window).unbind('resize', _mana_oldResizehandler[id]);
        _mana_oldResizehandler[id] = null;
    }
    _mana_oldResizehandler[id] = _resizeSpanAndHandles;
    jQuery(_resizeSpanAndHandles);

    jQuery(window).bind('resize', _resizeSpanAndHandles);
    jQuery(document).bind('m-ajax-after', _resizeSpanAndHandles);
    jQuery('#' + id + '-track').parent().on('m-prepare', function () {
        _resizeSpanAndHandles(true);
    });
    jQuery(document).on('click', '.toggle-content dl:first dt', function () {
        _resizeSpanAndHandles(true);
    });
    //jQuery('body').click(_resizeSpanAndHandles);
};
ManaPro.filterAttributeSlider = function (id, o) {
    function _indexOf(valueId) {
        var result = -1;
        o.existingValues.each(function(item, index) {
            if (item.id == valueId) {
                result = index;
            }
        });
        return result;
    }

    function _valueOf(index) {
        index = index.round();
        return o.existingValues[index].id;
    }

    function _labelOf(index) {
        index = index.round();
        return o.existingValues[index].label;
    }

    function _urlValueOf(index) {
        index = index.round();
        return o.existingValues[index].url;
    }

    function _prefixOf(index) {
        index = index.round();
        return o.existingValues[index].prefix;
    }

    function _change(value, undefined) {
        var indexes = [ value[0].round(), value[1].round()];
        s.values = indexes;
        s.value = s.values[0];
        s.handles[0].style[s.isVertical() ? 'top' : 'left'] = s.translateToPx(indexes[0], 0);
        s.handles[1].style[s.isVertical() ? 'top' : 'left'] = s.translateToPx(indexes[1], 1);
        s.drawSpans();
        if (indexes[0] <= _indexOf(o.rangeFrom) && indexes[1] >= _indexOf(o.rangeTo)) {
            window.setLocation(jQuery.base64_decode(o.clearUrl));
        }
        else {
            var formattedValue = _prefixOf(indexes[0]) + _urlValueOf(indexes[0]) + o.separator + _urlValueOf(indexes[1]);
            window.setLocation(jQuery.base64_decode(o.url).replace("__0__", formattedValue));
        }
    }

    var s = new Control.PriceSlider([id + '-from', id + '-to'], id + '-track', {
        spans:[id + '-span'],
        restricted:true,
        range:$R(_indexOf(o.rangeFrom), _indexOf(o.rangeTo)),
        sliderValue:[_indexOf(o.appliedFrom), _indexOf(o.appliedTo)]
    });

    s.options.onSlide = function (value) {
        var formattedValue = [ _labelOf(value[0]), _labelOf(value[1])];
        $(id + '-applied').update(o.appliedFormat.replace("__0__", formattedValue[0]).replace("__1__", formattedValue[1]));
    };
    s.options.onChange = _change;
    //
    function _resizeSpanAndHandles(forceResize) {
        var checkFrequency = 100, stabilityPeriod = 500;
        var checkingForStability = false, currentlyStableFor = 0;
        if (forceResize === true) {
            s.resize();
        }
        if (!_mana_sliderTimers[id]) {
            _mana_sliderTimers[id] = setInterval(function () {
                if (s.needsResize()) {
                    //console.log(id + ': resize');
                    s.resize();
                    checkingForStability = false;
                }
                else if (!checkingForStability){
                    checkingForStability = true;
                    currentlyStableFor = 0;
                    //console.log(id + ': checking for stability ' + currentlyStableFor);
                }
                else if (currentlyStableFor >= stabilityPeriod){
                    //console.log('stable');
                    clearInterval(_mana_sliderTimers[id]);
                    _mana_sliderTimers[id] = null;
                }
                else {
                    currentlyStableFor += checkFrequency;
                    //console.log('checking for stability ' + currentlyStableFor);
                }

            }, checkFrequency);
        }
    }
    if (_mana_oldResizehandler[id]) {
        jQuery(window).unbind('resize', _mana_oldResizehandler[id]);
        _mana_oldResizehandler[id] = null;
    }
    _mana_oldResizehandler[id] = _resizeSpanAndHandles;
    jQuery(_resizeSpanAndHandles);
    jQuery(window).bind('resize', _resizeSpanAndHandles);
    jQuery('#' + id + '-track').parent().on('m-prepare', function () {
        _resizeSpanAndHandles(true);
    });
};
ManaPro.filterRangeInput = function (id, o) {
    var _changing = false;
    var $_from = jQuery('#' + id + '-applied input.m-slider.m-from');
    var $_to = jQuery('#' + id + '-applied input.m-slider.m-to');
    var _value = [
        _parse($_from.val()),
        _parse($_to.val())
    ];

    function _parse(stringValue) {
        if (typeof stringValue != 'undefined') {
            stringValue = stringValue.replace(/,([^,]*)$/, '.$1');
        }
        return parseFloat(stringValue);
    }


    function _round(value) {
        if (o.formatThreshold && value >= o.formatThreshold) {
            return o.decimalDigits2 ? value.toFixed(o.decimalDigits2) : value.round();
        }
        else {
            return o.decimalDigits ? value.toFixed(o.decimalDigits) : value.round();
        }
    }

    function _change(undefined) {
        if (_changing) {
            return;
        }
        _changing = true;
        value = [
            _parse($_from.val()),
            _parse($_to.val())
        ];
        if (isNaN(value[0]) || isNaN(value[1])) {
            _changing = false;
            return;
        }
        if (value[0] == _value[0] && value[1] == _value[1]) {
            _changing = false;
            return;
        }

        else if (value[0] > value[1]) {
            var t = value[0];
            value[0] = value[1];
            value[1] = t;
        }
        if (value[0] <= o.rangeFrom && value[1] >= o.rangeTo) {
            window.setLocation(jQuery.base64_decode(o.clearUrl));
        }
        else {
            var formattedValue = [_round(value[0]), _round(value[1])];
            window.setLocation(jQuery.base64_decode(o.url).replace("__0__", formattedValue[0]).replace("__1__", formattedValue[1]));
        }
        _changing = false;
    }

    var _timer = null;
    $_from
        .change(function (event) {
            if (!_timer) {
                _timer = setTimeout(function () {
                    clearTimeout(_timer);
                    _timer = null;
                    _change();
                }, 100);
            }
        })
        .keypress(function (e) {
            if (e.which == 13) {
                if (!_timer) {
                    _timer = setTimeout(function () {
                        clearTimeout(_timer);
                        _timer = null;
                        _change();
                    }, 100);
                }
            }
            else if (e.which == 27) {
                $_from.val(_value[0]);
            }
        })
        .focus(function () {
            var self = this;
            var focusing = setTimeout(function () {
                clearTimeout(focusing);
                focusing = null;
                $(self).select();
            }, 100);
        });
    $_to
        .change(function () {
            if (_timer) {
                clearTimeout(_timer);
                _timer = null;
            }
            _change();
        })
        .keypress(function (e) {
            if (e.which == 13) {
                if (_timer) {
                    clearTimeout(_timer);
                    _timer = null;
                }
                _change();
            }
            else if (e.which == 27) {
                $_to.val(_value[1]);
            }
        })
        .focus(function () {
            if (_timer) {
                clearTimeout(_timer);
                _timer = null;
            }
            var self = this;
            var focusing = setTimeout(function () {
                clearTimeout(focusing);
                focusing = null;
                $(self).select();
            }, 100);
        })
        .blur(function () {
            if (_timer) {
                clearTimeout(_timer);
                _timer = null;
            }
            _change();
        });

};
