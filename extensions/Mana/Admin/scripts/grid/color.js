/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// the following function wraps code block that is executed once this javascript file is parsed. Lierally, this 
// notation says: here we define some anonymous function and call it once during file parsing. THis function has
// one parameter which is initialized with global jQuery object. Why use such complex notation: 
// 		a. 	all variables defined inside of the function belong to function's local scope, that is these variables
//			would not interfere with other global variables.
//		b.	we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
;(function($) {
	var _helper = null;
	var _td = null;
	var _confirmed = false;
	
	function _onHelperShow(td, helper) {
	    if ($.gridData(td, 'show_use_default')) {
	        $(helper).find('.use-default').show();
            $(helper).find('input.m-default').mMarkAttr('checked', $.gridData(td, 'is_default'));
            $(helper).find('input.m-default').mMarkAttr('disabled', $.gridData(td, 'is_default_disabled'));
        }
        else {
            $(helper).find('.use-default').hide();
        }
        var value = $.gridData(td, 'value');
        $('#m-color-helper .mcf-manual input')
            .val(value == '' ? 'transparent' : value)
            .mMarkAttr('disabled', $.gridData(td, 'show_use_default') && $(helper).find('input.m-default').is(':checked'));
		_helper = helper;
		_td = td;
	}
	function _onHelperHide(td, helper) {
	    if (_confirmed) {
	        var value = $(td).find('div').css('background-color');
	        value = value == 'transparent' ? '' : _hex(value);
	        value = value == '#00000000' ? '' : value;
        }
        else {
            var value = $.gridData(td, 'value');
        }
		$.gridData(td, {
		    value: value,
		    is_default: $(helper).find('input.m-default').is(':checked')
		});
		$(td).find('div').css({'background-color': value == ''? 'transparent' : value});
		_helper = null;
		_td = null;
		_confirmed = false;
	}
	function _hex(color) {
        color = color.toLowerCase();

        if (typeof color == 'undefined') return '';
        if (color.indexOf('#') > -1 && color.length > 6) return color;
        if (color.indexOf('rgb') < 0) return color;

        if (color.indexOf('#') > -1) {

          return '#' + color.substr(1, 1) + color.substr(1, 1) + color.substr(2, 1) + color.substr(2, 1) + color.substr(3, 1) + color.substr(3, 1);
        }

        var hexArray = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f"],
            decToHex = "#",
            code1 = 0;

        color = color.replace(/[^0-9,]/g, '').split(",");

        for (var n = 0; n < color.length; n++) {

          code1 = Math.floor(color[n] / 16);
          decToHex += hexArray[code1] + hexArray[color[n] - code1 * 16];
        }

        return decToHex;
	}
    function _paletteColor (x, y) {
        var colorR = colorG = colorB = 255;

        if (x < 32) {
            colorG = x * 8;
            colorB = 0;
        } else if (x < 64) {
            colorR = 256 - (x - 32 ) * 8;
            colorB = 0;
        } else if (x < 96) {
            colorR = 0;
            colorB = (x - 64) * 8;
        } else if (x < 128) {
            colorR = 0;
            colorG = 256 - (x - 96) * 8;
        } else if (x < 160) {
            colorR = (x - 128) * 8;
            colorG = 0;
        } else {
            colorG = 0;
            colorB = 256 - (x - 160) * 8;
        }
        if (y < 64) {
            colorR += (256 - colorR) * (64 - y) / 64;
            colorG += (256 - colorG) * (64 - y) / 64;
            colorB += (256 - colorB) * (64 - y) / 64;
        } else if (y <= 128) {
            colorR -= colorR * (y - 64) / 64;
            colorG -= colorG * (y - 64) / 64;
            colorB -= colorB * (y - 64) / 64;
        } else if (y > 128) {
            colorR = colorG = colorB = 256 - ( x / 192 * 256 );
        }
        colorR = Math.round(Math.min(colorR, 255));
        colorG = Math.round(Math.min(colorG, 255));
        colorB = Math.round(Math.min(colorB, 255));

        colorR = colorR.toString(16);
        colorG = colorG.toString(16);
        colorB = colorB.toString(16);

        if (colorR.length < 2) colorR = 0 + colorR;
        if (colorG.length < 2) colorG = 0 + colorG;
        if (colorB.length < 2) colorB = 0 + colorB;

        return "#" + colorR + colorG + colorB;
    }
	// the following function is executed when DOM ir ready. If not use this wrapper, code inside could fail if
	// executed when referenced DOM elements are still being loaded.
	$(function() {
		$(document).on('mouseover', '.ct-color', function() {
			if ($.gridData(this, 'show_helper')) { 
				$.helperPopup({
					host: this, 
					helper: '#m-color-helper',
					onShow: _onHelperShow,
					onHide: _onHelperHide
				});
			}
		});

        $('#m-color-helper .mc-palette')
            .mousemove(function(e) {
                if ($('#m-color-helper-checkbox').is(':checked')) {
                    return; 
                }
                var offset = $(this).offset();
                var color = _paletteColor(e.pageX - offset.left, e.pageY - offset.top);
                $('#m-color-helper .mcf-manual input').val(color);
                if (_td) {
                    $(_td).find('div').css({'background-color': color});
                }
            })
            .click(function() {
                _confirmed = true;
                $.hideHelperPopup();
            });
        $('#m-color-helper .mc-gray')
            .mousemove(function(e) {
                if ($('#m-color-helper-checkbox').is(':checked')) {
                    return;
                }
                var offset = $(this).offset();
                var color = _paletteColor(e.pageX - offset.left, e.pageY - offset.top + 128);
                $('#m-color-helper .mcf-manual input').val(color);
                if (_td) {
                    $(_td).find('div').css({'background-color': color});
                }
            })
            .click(function() {
                _confirmed = true;
                $.hideHelperPopup();
            });
        $('#m-color-helper .mc-predefined li')
            .mousemove(function(e) {
                if ($('#m-color-helper-checkbox').is(':checked')) {
                    return;
                }
                var color = _hex($(this).css('background-color'));
                $('#m-color-helper .mcf-manual input').val(color);
                if (_td) {
                    $(_td).find('div').css({'background-color': color});
                }
            })
            .click(function() {
                _confirmed = true;
                $.hideHelperPopup();
            });
        $('#m-color-helper .mcf-transparent')
            .mousemove(function(e) {
                if ($('#m-color-helper-checkbox').is(':checked')) {
                    return;
                }
                var color = 'transparent';
                $('#m-color-helper .mcf-manual input').val(color);
                if (_td) {
                    $(_td).find('div').css({'background-color': color});
                }
            })
            .click(function() {
                _confirmed = true;
                $.hideHelperPopup();
            });
        $('#m-color-helper .mcf-manual input').keyup(function(e) {
            var color = $(this).val();
            if (_td) {
                $(_td).find('div').css({'background-color': color});
            }
            if (e.which == 13) { // ENTER
                _confirmed = true;
                $.hideHelperPopup();
            }
        });
        $('#m-color-helper input.m-default').click(function() {
            $('#m-color-helper .mcf-manual input').mMarkAttr('disabled', $(this).is(':checked'));
        });
	});
})(jQuery);
