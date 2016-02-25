/**
 * @category    Mana
 * @package     ManaPro_DependentDropdowns
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

    function _showHideFieldset(ifField, equals, thenShowHideFieldset) {
        if ($(ifField).val() == equals) {
            if ($(thenShowHideFieldset).prev().prev().find('a').hasClass('open')) {
                $(thenShowHideFieldset).show()
                    .prev().show();
            }
            else {
                $(thenShowHideFieldset)
                    .prev().show();
            }
        }
        else {
            $(thenShowHideFieldset).hide()
                .prev().hide();
        }
    }

    function _changedFieldset(equals, thenShowHideFieldset) {
        return function () {
            _showHideFieldset(this, equals, thenShowHideFieldset);
        };
    }

    $(function () {
        _showHideFieldset('#frontend_input', 'select', '#m_dependent_fieldset');
    });
    $(document).on('change', '#frontend_input', _changedFieldset('select', '#m_dependent_fieldset'));
})(jQuery);
