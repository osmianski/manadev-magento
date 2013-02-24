/**
 * @category    Mana
 * @package     ManaPro_FilterGroup
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
;(function ($, undefined) {
    var _states = {};

    function expand(element, duration) {
        $(element).removeClass('m-collapsed').addClass('m-expanded');
        $(element).next().slideDown(duration);
    }
    function collapse(element, duration) {
        $(element).removeClass('m-expanded').addClass('m-collapsed');
        $(element).next().slideUp(duration);
    }
    function saveState(element) {
        if ($(element).hasClass('m-expanded')) {
            _states[$(element).attr('data-id')] = true;
            expand(element, 0);
        }
        else if ($(element).hasClass('m-collapsed')) {
            _states[$(element).attr('data-id')] = false;
            collapse(element, 0);
        }
        else if ($(element).attr('data-collapsed') == 'collapsed') {
            _states[$(element).attr('data-id')] = false;
            collapse(element, 0);
        }
        else {
            _states[$(element).attr('data-id')] = true;
            expand(element, 0);
        }
    }

    function restoreState(element) {
        if (_states[$(element).attr('data-id')] !== undefined) {
            if (_states[$(element).attr('data-id')]) {
                expand(element, 0);
            }
            else {
                collapse(element, 0);
            }
        }
        else {
            saveState(element);
        }
    }

    $(function() {
        $('.block-layered-nav .m-collapseable-group').each(function(index, element) {
            saveState(element);
        });
    });
    $(document).bind('m-ajax-after', function (e, selectors) {
        $('.block-layered-nav .m-collapseable-group').each(function (index, element) {
            restoreState(element);
        });
    });
    $('.block-layered-nav .m-collapseable-group').live('click', function() {
        var element = this;
        if ($(element).hasClass('m-expanded')) {
            _states[$(element).attr('data-id')] = false;
            collapse(element, 200);
        }
        else {
            _states[$(element).attr('data-id')] = true;
            expand(element, 200);
        }
    });
})(jQuery);
