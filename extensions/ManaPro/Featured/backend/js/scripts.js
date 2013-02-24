/**
 * @category    Mana
 * @package     ManaPro_Featured
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * the following function wraps code block that is executed once this javascript file is parsed. Lierally, this
 * notation says: here we define some anonymous function and call it once during file parsing. THis function has
 * one parameter which is initialized with global jQuery object. Why use such complex notation:
 *         a.     all variables defined inside of the function belong to function's local scope, that is these variables
 *            would not interfere with other global variables.
 *        b.    we use jQuery $ notation in not conflicting way (along with prototype, ext, etc.)
 */
;(function($) {

    function _showHideField(ifField, equals, thenShowHideField) {
        if ($(ifField).val() == equals) {
            $(thenShowHideField).parents('tr').first().show();
        }
        else {
            $(thenShowHideField).parents('tr').first().hide();
        }
    }

    function _showHideFieldset(ifField, equals, thenShowHideFieldset) {
        if ($(ifField).val() == equals) {
            if ($(thenShowHideFieldset).prev().prev().find('a').hasClass('open')) {
                $(thenShowHideFieldset).show()
                    .prev().prev().show();
            }
            else {
                $(thenShowHideFieldset)
                    .prev().prev().show();
            }
        }
        else {
            $(thenShowHideFieldset).hide()
                .prev().prev().hide();
        }
    }

    function _changedField(equals, thenShowHideField) {
        return function() {
            _showHideField(this, equals, thenShowHideField);
        };
    }

    function _changedFieldset(equals, thenShowHideFieldset) {
        return function () {
            _showHideFieldset(this, equals, thenShowHideFieldset);
        };
    }

    // GENERAL SHOW/HIDE LOGIC
    //////////////////////////

    function a() {
        if ($('#mana_featured_category_template').val() == 'custom') {
            $('#mana_featured_category_custom').parents('tr').first().show();
        }
        else {
            $('#mana_featured_category_custom').parents('tr').first().hide();
        }
    }

    $(function() {
        _showHideField('#mana_featured_category_template', 'custom', '#mana_featured_category_custom');
        _showHideField('#mana_featured_category_show', 'specified', '#mana_featured_category_count');
    });
    $('#mana_featured_category_template').live('change', _changedField('custom', '#mana_featured_category_custom'));
    $('#mana_featured_category_show').live('change', _changedField('specified', '#mana_featured_category_count'));

    // CAROUSEL SHOW/HIDE LOGIC
    ///////////////////////////

    $(function () {
        _showHideFieldset('#mana_featured_category_template', 'carousel', '#mana_featured_category_carousel_fields');
        _showHideFieldset('#mana_featured_category_template', 'carousel', '#mana_featured_category_carousel_effect');
        _showHideFieldset('#mana_featured_category_template', 'carousel', '#mana_featured_category_carousel_helper');
        _showHideFieldset('#mana_featured_category_template', 'carousel', '#mana_featured_category_carousel_navigation');
        _showHideFieldset('#mana_featured_category_template', 'carousel', '#mana_featured_category_carousel_decoration');
        _showHideFieldset('#mana_featured_category_template', 'carousel', '#mana_featured_category_carousel_other');

        _showHideField('#mana_featured_category_carousel_effect_hide', 'custom', '#mana_featured_category_carousel_effect_hide_custom');
        _showHideField('#mana_featured_category_carousel_effect_hide', 'random', '#mana_featured_category_carousel_effect_hide_random');
        _showHideField('#mana_featured_category_carousel_effect_hide', 'blind', '#mana_featured_category_carousel_effect_hide_blind_direction');
        _showHideField('#mana_featured_category_carousel_effect_hide', 'clip', '#mana_featured_category_carousel_effect_hide_clip_direction');
        _showHideField('#mana_featured_category_carousel_effect_hide', 'drop', '#mana_featured_category_carousel_effect_hide_drop_direction');
        _showHideField('#mana_featured_category_carousel_effect_show', 'custom', '#mana_featured_category_carousel_effect_show_custom');
        _showHideField('#mana_featured_category_carousel_effect_show', 'random', '#mana_featured_category_carousel_effect_show_random');
        _showHideField('#mana_featured_category_carousel_effect_show', 'blind', '#mana_featured_category_carousel_effect_show_blind_direction');
        _showHideField('#mana_featured_category_carousel_effect_show', 'clip', '#mana_featured_category_carousel_effect_show_clip_direction');
        _showHideField('#mana_featured_category_carousel_effect_show', 'drop', '#mana_featured_category_carousel_effect_show_drop_direction');

        _showHideField('#mana_featured_category_carousel_helper_template', 'custom', '#mana_featured_category_carousel_helper_custom');

        _showHideField('#mana_featured_category_carousel_decoration_top_left', 'custom', '#mana_featured_category_carousel_decoration_top_left_custom');
        _showHideField('#mana_featured_category_carousel_decoration_top', 'custom', '#mana_featured_category_carousel_decoration_top_custom');
        _showHideField('#mana_featured_category_carousel_decoration_top_right', 'custom', '#mana_featured_category_carousel_decoration_top_right_custom');
        _showHideField('#mana_featured_category_carousel_decoration_right', 'custom', '#mana_featured_category_carousel_decoration_right_custom');
        _showHideField('#mana_featured_category_carousel_decoration_bottom_right', 'custom', '#mana_featured_category_carousel_decoration_bottom_right_custom');
        _showHideField('#mana_featured_category_carousel_decoration_bottom', 'custom', '#mana_featured_category_carousel_decoration_bottom_custom');
        _showHideField('#mana_featured_category_carousel_decoration_bottom_left', 'custom', '#mana_featured_category_carousel_decoration_bottom_left_custom');
        _showHideField('#mana_featured_category_carousel_decoration_left', 'custom', '#mana_featured_category_carousel_decoration_left_custom');
        _showHideField('#mana_featured_category_carousel_decoration_shadow', 'custom', '#mana_featured_category_carousel_decoration_shadow_custom');

        _showHideField('#mana_featured_category_carousel_si_decoration_top_left', 'custom', '#mana_featured_category_carousel_si_decoration_top_left_custom');
        _showHideField('#mana_featured_category_carousel_si_decoration_top', 'custom', '#mana_featured_category_carousel_si_decoration_top_custom');
        _showHideField('#mana_featured_category_carousel_si_decoration_top_right', 'custom', '#mana_featured_category_carousel_si_decoration_top_right_custom');
        _showHideField('#mana_featured_category_carousel_si_decoration_right', 'custom', '#mana_featured_category_carousel_si_decoration_right_custom');
        _showHideField('#mana_featured_category_carousel_si_decoration_bottom_right', 'custom', '#mana_featured_category_carousel_si_decoration_bottom_right_custom');
        _showHideField('#mana_featured_category_carousel_si_decoration_bottom', 'custom', '#mana_featured_category_carousel_si_decoration_bottom_custom');
        _showHideField('#mana_featured_category_carousel_si_decoration_bottom_left', 'custom', '#mana_featured_category_carousel_si_decoration_bottom_left_custom');
        _showHideField('#mana_featured_category_carousel_si_decoration_left', 'custom', '#mana_featured_category_carousel_si_decoration_left_custom');

        _showHideField('#mana_featured_category_carousel_navigation_template', 'custom', '#mana_featured_category_carousel_navigation_custom_prev');
        _showHideField('#mana_featured_category_carousel_navigation_template', 'custom', '#mana_featured_category_carousel_navigation_custom_next');
    });
    $('#mana_featured_category_template').live('change', _changedFieldset('carousel', '#mana_featured_category_carousel_fields'));
    $('#mana_featured_category_template').live('change', _changedFieldset('carousel', '#mana_featured_category_carousel_effect'));
    $('#mana_featured_category_template').live('change', _changedFieldset('carousel', '#mana_featured_category_carousel_helper'));
    $('#mana_featured_category_template').live('change', _changedFieldset('carousel', '#mana_featured_category_carousel_navigation'));
    $('#mana_featured_category_template').live('change', _changedFieldset('carousel', '#mana_featured_category_carousel_decoration'));
    $('#mana_featured_category_template').live('change', _changedFieldset('carousel', '#mana_featured_category_carousel_other'));

    $('#mana_featured_category_carousel_effect_hide').live('change', _changedField('custom', '#mana_featured_category_carousel_effect_hide_custom'));
    $('#mana_featured_category_carousel_effect_hide').live('change', _changedField('random', '#mana_featured_category_carousel_effect_hide_random'));
    $('#mana_featured_category_carousel_effect_hide').live('change', _changedField('blind', '#mana_featured_category_carousel_effect_hide_blind_direction'));
    $('#mana_featured_category_carousel_effect_hide').live('change', _changedField('clip', '#mana_featured_category_carousel_effect_hide_clip_direction'));
    $('#mana_featured_category_carousel_effect_hide').live('change', _changedField('drop', '#mana_featured_category_carousel_effect_hide_drop_direction'));
    $('#mana_featured_category_carousel_effect_show').live('change', _changedField('custom', '#mana_featured_category_carousel_effect_show_custom'));
    $('#mana_featured_category_carousel_effect_show').live('change', _changedField('random', '#mana_featured_category_carousel_effect_show_random'));
    $('#mana_featured_category_carousel_effect_show').live('change', _changedField('blind', '#mana_featured_category_carousel_effect_show_blind_direction'));
    $('#mana_featured_category_carousel_effect_show').live('change', _changedField('clip', '#mana_featured_category_carousel_effect_show_clip_direction'));
    $('#mana_featured_category_carousel_effect_show').live('change', _changedField('drop', '#mana_featured_category_carousel_effect_show_drop_direction'));

    $('#mana_featured_category_carousel_helper_template').live('change', _changedField('custom', '#mana_featured_category_carousel_helper_custom'));

    $('#mana_featured_category_carousel_decoration_top_left').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_top_left_custom'));
    $('#mana_featured_category_carousel_decoration_top').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_top_custom'));
    $('#mana_featured_category_carousel_decoration_top_right').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_top_right_custom'));
    $('#mana_featured_category_carousel_decoration_right').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_right_custom'));
    $('#mana_featured_category_carousel_decoration_bottom_right').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_bottom_right_custom'));
    $('#mana_featured_category_carousel_decoration_bottom').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_bottom_custom'));
    $('#mana_featured_category_carousel_decoration_bottom_left').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_bottom_left_custom'));
    $('#mana_featured_category_carousel_decoration_left').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_left_custom'));
    $('#mana_featured_category_carousel_decoration_shadow').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_shadow_custom'));

    $('#mana_featured_category_carousel_si_decoration_top_left').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_top_left_custom'));
    $('#mana_featured_category_carousel_si_decoration_top').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_top_custom'));
    $('#mana_featured_category_carousel_si_decoration_top_right').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_top_right_custom'));
    $('#mana_featured_category_carousel_si_decoration_right').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_right_custom'));
    $('#mana_featured_category_carousel_si_decoration_bottom_right').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_bottom_right_custom'));
    $('#mana_featured_category_carousel_si_decoration_bottom').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_bottom_custom'));
    $('#mana_featured_category_carousel_si_decoration_bottom_left').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_bottom_left_custom'));
    $('#mana_featured_category_carousel_si_decoration_left').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_left_custom'));

    $('#mana_featured_category_carousel_navigation_template').live('change', _changedField('custom', '#mana_featured_category_carousel_navigation_custom_prev'));
    $('#mana_featured_category_carousel_navigation_template').live('change', _changedField('custom', '#mana_featured_category_carousel_navigation_custom_next'));

})(jQuery);
