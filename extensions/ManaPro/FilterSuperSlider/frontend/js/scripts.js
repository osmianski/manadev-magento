/**
 * @category    Mana
 * @package     ManaPro_FilterSuperSlider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
;var ManaPro = ManaPro || {};
ManaPro.filterSuperSlider = function(id, o) {
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
            value = parseFloat(o.existingValues[found]);
        }
        if (o.formatThreshold && value >= o.formatThreshold) {
            return o.decimalDigits2 ? value.toFixed(o.decimalDigits2) : value.round();
        }
        else {
            return o.decimalDigits ? value.toFixed(o.decimalDigits) : value.round();
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
        if (value === undefined) {
            value = [
                parseFloat(jQuery('#'+id+'-applied input.m-slider.m-from').val()),
                parseFloat(jQuery('#'+id+'-applied input.m-slider.m-to').val())
            ];
            if (value[0] == NaN || value[1] == NaN) {
                return;
            }
            else if (value[0] > value[1]) {
                var t = value[0];
                value[0] = value[1];
                value[1] = t;
            }
        }
        if (value[0] <= o.rangeFrom && value[1] >= o.rangeTo) {
            window.setLocation(o.clearUrl);
        }
        else {
            var formattedValue = [_round(value[0]), _round(value[1])];
            window.setLocation(o.url.replace("__0__", formattedValue[0]).replace("__1__", formattedValue[1]));
        }
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
    jQuery('#'+id+'-applied input.m-slider.m-from').change(function(event) {
        _timer = setTimeout(function() {
            clearTimeout(_timer);
            _timer = null;
            _change();
        }, 100);
    });
    jQuery('#'+id+'-applied input.m-slider.m-to').change(function() {
        _timer = null;
        _change();
    })
    .focus(function() {
        clearTimeout(_timer);
    })
    .blur(function() {
        if (_timer) {
            _timer = null;
            _change();
        }
    });

};
ManaPro.filterAttributeSlider = function (id, o) {
    function _indexOf(valueId) {
        var result = -1;
        o.existingValues.each(function(item, index) {
            if (item.value == valueId) {
                result = index;
            }
        });
        return result;
    }

    function _valueOf(index) {
        index = index.round();
        return o.existingValues[index].value;
    }

    function _labelOf(index) {
        index = index.round();
        return o.existingValues[index].label;
    }

    function _urlValueOf(index) {
        index = index.round();
        return o.existingValues[index].urlValue;
    }

    function _change(value, undefined) {
        var indexes = [ value[0].round(), value[1].round()];
        s.values = indexes;
        s.value = s.values[0];
        s.handles[0].style[s.isVertical() ? 'top' : 'left'] = s.translateToPx(indexes[0], 0);
        s.handles[1].style[s.isVertical() ? 'top' : 'left'] = s.translateToPx(indexes[1], 1);
        s.drawSpans();
        if (indexes[0] <= _indexOf(o.rangeFrom) && indexes[1] >= _indexOf(o.rangeTo)) {
            window.setLocation(o.clearUrl);
        }
        else {
            /*var formattedValue = '';
            for (var i = indexes[0]; i <= indexes[1]; i++) {
                if (formattedValue.length) {
                    formattedValue += '_';
                }
                formattedValue += _urlValueOf(i);
            }*/
            var formattedValue = _urlValueOf(indexes[0]) + '_' + _urlValueOf(indexes[1]);
            window.setLocation(o.url.replace("__0__", formattedValue));
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
};