/**
 * @category    Mana
 * @package     ManaPro_FilterSuperSlider
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
    function _change() {
        var $display = $('#mf_general_display');
        var $range = $('#mfs_range');
        if ($range.hasClass('m-decimal')) {
            if ($display.val() != 'slider' && $display.val() != 'range' && $display.val() != 'min_max_slider') {
                $range.show();
            }
            else {
                $range.hide();
            }
            if ($display.val() != 'slider') {
                $('#mf_general_slider_manual_entry').parent().parent().hide();
                $('#mf_general_slider_use_existing_values').parent().parent().hide();
            }
            else {
                $('#mf_general_slider_manual_entry').parent().parent().show();
                $('#mf_general_slider_use_existing_values').parent().parent().show();
            }
        }
        var $minMaxRole = $('#mf_general_min_max_slider_role');
        if ($display.val() != 'min_max_slider') {
            $minMaxRole.parent().parent().hide();
            $('#mf_general_min_slider_code').parent().parent().hide();
        }
        else {
            $minMaxRole.parent().parent().show();
            if ($minMaxRole.val() == 'max') {
                $('#mf_general_min_slider_code').parent().parent().show();
            }
            else {
                $('#mf_general_min_slider_code').parent().parent().hide();
            }
        }
    }
    function _minMaxRoleChange() {
        var $display = $('#mf_general_display');
        var $minMaxRole = $('#mf_general_min_max_slider_role');
        if ($display.val() != 'min_max_slider') {
            $('#mf_general_min_slider_code').parent().parent().hide();
        }
        else {
            if ($minMaxRole.val() == 'max') {
                $('#mf_general_min_slider_code').parent().parent().show();
            }
            else {
                $('#mf_general_min_slider_code').parent().parent().hide();
            }
        }
    }
    $(_change);
    $(document).on('change', '#mf_general_display', _change);
    $(document).on('change', '#mf_general_min_max_slider_role', _minMaxRoleChange);
})(jQuery);
