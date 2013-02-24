/**
 * @category    Mana
 * @package     ManaPro_Slider
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
;(function ($) {
    //region Show/Hide Helpers
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
        return function () {
            _showHideField(this, equals, thenShowHideField);
        };
    }

    function _changedFieldset(equals, thenShowHideFieldset) {
        return function () {
            _showHideFieldset(this, equals, thenShowHideFieldset);
        };
    }

    function _showHideFieldNowAndOnChange(ifField, equals, thenShowHideField) {
        _showHideField('#manapro_slider_'+ ifField, equals, '#manapro_slider_' + thenShowHideField);
        $('#manapro_slider_' + ifField).live('change', _changedField(equals, '#manapro_slider_' + thenShowHideField));
    }
    //endregion

    //region Show/Hide Logic
    function _initShowHide() {
        _showHideFieldNowAndOnChange('effect_hide select', 'random', 'effect_hide_random select');
        _showHideFieldNowAndOnChange('effect_hide select', 'blind', 'effect_hide_blind_direction select');
        _showHideFieldNowAndOnChange('effect_hide select', 'clip', 'effect_hide_clip_direction select');
        _showHideFieldNowAndOnChange('effect_hide select', 'drop', 'effect_hide_drop_direction select');

        _showHideFieldNowAndOnChange('effect_show select', 'random', 'effect_show_random select');
        _showHideFieldNowAndOnChange('effect_show select', 'blind', 'effect_show_blind_direction select');
        _showHideFieldNowAndOnChange('effect_show select', 'clip', 'effect_show_clip_direction select');
        _showHideFieldNowAndOnChange('effect_show select', 'drop', 'effect_show_drop_direction select');
    }

    $(_initShowHide);
    $.mInitSliderWidgetPopupShowHide = function() {
        _initShowHide();
    };
//        _showHideFieldset('#mana_featured_category_template', 'carousel', '#mana_featured_category_carousel_fields');
//        $('#mana_featured_category_template').live('change', _changedFieldset('carousel', '#mana_featured_category_carousel_fields'));

//        _showHideFieldset('#mana_featured_category_template', 'carousel', '#mana_featured_category_carousel_effect');
//        _showHideFieldset('#mana_featured_category_template', 'carousel', '#mana_featured_category_carousel_helper');
//        _showHideFieldset('#mana_featured_category_template', 'carousel', '#mana_featured_category_carousel_navigation');
//        _showHideFieldset('#mana_featured_category_template', 'carousel', '#mana_featured_category_carousel_decoration');
//        _showHideFieldset('#mana_featured_category_template', 'carousel', '#mana_featured_category_carousel_other');
//
//        _showHideField('#mana_featured_category_carousel_effect_show', 'custom', '#mana_featured_category_carousel_effect_show_custom');
//        _showHideField('#mana_featured_category_carousel_effect_show', 'random', '#mana_featured_category_carousel_effect_show_random');
//        _showHideField('#mana_featured_category_carousel_effect_show', 'blind', '#mana_featured_category_carousel_effect_show_blind_direction');
//        _showHideField('#mana_featured_category_carousel_effect_show', 'clip', '#mana_featured_category_carousel_effect_show_clip_direction');
//        _showHideField('#mana_featured_category_carousel_effect_show', 'drop', '#mana_featured_category_carousel_effect_show_drop_direction');
//
//        _showHideField('#mana_featured_category_carousel_helper_template', 'custom', '#mana_featured_category_carousel_helper_custom');
//
//        _showHideField('#mana_featured_category_carousel_decoration_top_left', 'custom', '#mana_featured_category_carousel_decoration_top_left_custom');
//        _showHideField('#mana_featured_category_carousel_decoration_top', 'custom', '#mana_featured_category_carousel_decoration_top_custom');
//        _showHideField('#mana_featured_category_carousel_decoration_top_right', 'custom', '#mana_featured_category_carousel_decoration_top_right_custom');
//        _showHideField('#mana_featured_category_carousel_decoration_right', 'custom', '#mana_featured_category_carousel_decoration_right_custom');
//        _showHideField('#mana_featured_category_carousel_decoration_bottom_right', 'custom', '#mana_featured_category_carousel_decoration_bottom_right_custom');
//        _showHideField('#mana_featured_category_carousel_decoration_bottom', 'custom', '#mana_featured_category_carousel_decoration_bottom_custom');
//        _showHideField('#mana_featured_category_carousel_decoration_bottom_left', 'custom', '#mana_featured_category_carousel_decoration_bottom_left_custom');
//        _showHideField('#mana_featured_category_carousel_decoration_left', 'custom', '#mana_featured_category_carousel_decoration_left_custom');
//        _showHideField('#mana_featured_category_carousel_decoration_shadow', 'custom', '#mana_featured_category_carousel_decoration_shadow_custom');
//
//        _showHideField('#mana_featured_category_carousel_si_decoration_top_left', 'custom', '#mana_featured_category_carousel_si_decoration_top_left_custom');
//        _showHideField('#mana_featured_category_carousel_si_decoration_top', 'custom', '#mana_featured_category_carousel_si_decoration_top_custom');
//        _showHideField('#mana_featured_category_carousel_si_decoration_top_right', 'custom', '#mana_featured_category_carousel_si_decoration_top_right_custom');
//        _showHideField('#mana_featured_category_carousel_si_decoration_right', 'custom', '#mana_featured_category_carousel_si_decoration_right_custom');
//        _showHideField('#mana_featured_category_carousel_si_decoration_bottom_right', 'custom', '#mana_featured_category_carousel_si_decoration_bottom_right_custom');
//        _showHideField('#mana_featured_category_carousel_si_decoration_bottom', 'custom', '#mana_featured_category_carousel_si_decoration_bottom_custom');
//        _showHideField('#mana_featured_category_carousel_si_decoration_bottom_left', 'custom', '#mana_featured_category_carousel_si_decoration_bottom_left_custom');
//        _showHideField('#mana_featured_category_carousel_si_decoration_left', 'custom', '#mana_featured_category_carousel_si_decoration_left_custom');
//
//        _showHideField('#mana_featured_category_carousel_navigation_template', 'custom', '#mana_featured_category_carousel_navigation_custom_prev');
//        _showHideField('#mana_featured_category_carousel_navigation_template', 'custom', '#mana_featured_category_carousel_navigation_custom_next');
//
//        $('#mana_featured_category_template').live('change', _changedFieldset('carousel', '#mana_featured_category_carousel_effect'));
//        $('#mana_featured_category_template').live('change', _changedFieldset('carousel', '#mana_featured_category_carousel_helper'));
//        $('#mana_featured_category_template').live('change', _changedFieldset('carousel', '#mana_featured_category_carousel_navigation'));
//        $('#mana_featured_category_template').live('change', _changedFieldset('carousel', '#mana_featured_category_carousel_decoration'));
//        $('#mana_featured_category_template').live('change', _changedFieldset('carousel', '#mana_featured_category_carousel_other'));
//
//        $('#mana_featured_category_carousel_effect_hide').live('change', _changedField('custom', '#mana_featured_category_carousel_effect_hide_custom'));
//        $('#mana_featured_category_carousel_effect_hide').live('change', _changedField('blind', '#mana_featured_category_carousel_effect_hide_blind_direction'));
//        $('#mana_featured_category_carousel_effect_hide').live('change', _changedField('clip', '#mana_featured_category_carousel_effect_hide_clip_direction'));
//        $('#mana_featured_category_carousel_effect_hide').live('change', _changedField('drop', '#mana_featured_category_carousel_effect_hide_drop_direction'));
//        $('#mana_featured_category_carousel_effect_show').live('change', _changedField('custom', '#mana_featured_category_carousel_effect_show_custom'));
//        $('#mana_featured_category_carousel_effect_show').live('change', _changedField('random', '#mana_featured_category_carousel_effect_show_random'));
//        $('#mana_featured_category_carousel_effect_show').live('change', _changedField('blind', '#mana_featured_category_carousel_effect_show_blind_direction'));
//        $('#mana_featured_category_carousel_effect_show').live('change', _changedField('clip', '#mana_featured_category_carousel_effect_show_clip_direction'));
//        $('#mana_featured_category_carousel_effect_show').live('change', _changedField('drop', '#mana_featured_category_carousel_effect_show_drop_direction'));
//
//        $('#mana_featured_category_carousel_helper_template').live('change', _changedField('custom', '#mana_featured_category_carousel_helper_custom'));
//
//        $('#mana_featured_category_carousel_decoration_top_left').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_top_left_custom'));
//        $('#mana_featured_category_carousel_decoration_top').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_top_custom'));
//        $('#mana_featured_category_carousel_decoration_top_right').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_top_right_custom'));
//        $('#mana_featured_category_carousel_decoration_right').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_right_custom'));
//        $('#mana_featured_category_carousel_decoration_bottom_right').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_bottom_right_custom'));
//        $('#mana_featured_category_carousel_decoration_bottom').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_bottom_custom'));
//        $('#mana_featured_category_carousel_decoration_bottom_left').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_bottom_left_custom'));
//        $('#mana_featured_category_carousel_decoration_left').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_left_custom'));
//        $('#mana_featured_category_carousel_decoration_shadow').live('change', _changedField('custom', '#mana_featured_category_carousel_decoration_shadow_custom'));
//
//        $('#mana_featured_category_carousel_si_decoration_top_left').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_top_left_custom'));
//        $('#mana_featured_category_carousel_si_decoration_top').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_top_custom'));
//        $('#mana_featured_category_carousel_si_decoration_top_right').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_top_right_custom'));
//        $('#mana_featured_category_carousel_si_decoration_right').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_right_custom'));
//        $('#mana_featured_category_carousel_si_decoration_bottom_right').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_bottom_right_custom'));
//        $('#mana_featured_category_carousel_si_decoration_bottom').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_bottom_custom'));
//        $('#mana_featured_category_carousel_si_decoration_bottom_left').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_bottom_left_custom'));
//        $('#mana_featured_category_carousel_si_decoration_left').live('change', _changedField('custom', '#mana_featured_category_carousel_si_decoration_left_custom'));
//
//        $('#mana_featured_category_carousel_navigation_template').live('change', _changedField('custom', '#mana_featured_category_carousel_navigation_custom_prev'));
//        $('#mana_featured_category_carousel_navigation_template').live('change', _changedField('custom', '#mana_featured_category_carousel_navigation_custom_next'));

    //endregion

    //region Product Grid Logic
    $('#mSliderProductGrid .filter-actions .m-add').live('click', function () {
        $.mChooseProducts({
            url: $.options('#mSliderProductGrid').chooserUrl,
            params:function () {
                return { 'm-edit':$.gridEditedData('mSliderProductGrid') };
            },
            result:function (ids) {
                if (ids) {
                    $.gridAction('mSliderProductGrid', 'addProducts', { ids: ids });
                }
            }
        });
    });
    $('#mSliderProductGrid .filter-actions .m-remove').live('click', function () {
        $.gridAction('mSliderProductGrid', 'remove');
    });
    //endregion

    //region CMS Block Grid Logic
    $('#mSliderCmsblockGrid .filter-actions .m-add').live('click', function () {
        $.mChooseCmsBlocks({
            url:$.options('#mSliderCmsblockGrid').chooserUrl,
            params:function () {
                return { 'm-edit':$.gridEditedData('mSliderCmsblockGrid') };
            },
            result:function (ids) {
                if (ids) {
                    $.gridAction('mSliderCmsblockGrid', 'addCmsBlocks', { ids:ids });
                }
            }
        });
    });
    $('#mSliderCmsblockGrid .filter-actions .m-remove').live('click', function () {
        $.gridAction('mSliderCmsblockGrid', 'remove');
    });
    //endregion

    //region HTML Block Grid Logic
    $('#mSliderHtmlblockGrid .filter-actions .m-add').live('click', function () {
        $.gridAction('mSliderHtmlblockGrid', 'add');
    });
    $('#mSliderHtmlblockGrid .filter-actions .m-remove').live('click', function () {
        $.gridAction('mSliderHtmlblockGrid', 'remove');
    });
    //endregion

})(jQuery);
