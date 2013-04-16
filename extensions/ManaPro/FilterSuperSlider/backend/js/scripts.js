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
        if ($('#mfs_range').hasClass('m-decimal')) {
            var $this = $('#mf_general_display');
            if ($this.val() != 'slider' && $this.val() != 'range') {
                $('#mfs_range').show();
            }
            else {
                $('#mfs_range').hide();
            }
            if ($this.val() != 'slider') {
                $('#mf_general_slider_manual_entry').parent().parent().hide();
                $('#mf_general_slider_use_existing_values').parent().parent().hide();
            }
            else {
                $('#mf_general_slider_manual_entry').parent().parent().show();
                $('#mf_general_slider_use_existing_values').parent().parent().show();
            }
        }
    }
    $(_change);
    $('#mf_general_display').live('change', _change);
})(jQuery);
