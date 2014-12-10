/**
 * @category    Mana
 * @package     ManaPro_FilterExpandCollapse
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
            if (!Mana.rwdIsMobile) {
                expand(element, 0);
            }
        }
        else if ($(element).hasClass('m-collapsed')) {
            _states[$(element).attr('data-id')] = false;
            if (!Mana.rwdIsMobile) {
                collapse(element, 0);
            }
        }
        else if ($(element).attr('data-collapsed') == 'collapsed') {
            _states[$(element).attr('data-id')] = false;
            if (!Mana.rwdIsMobile) {
                collapse(element, 0);
            }
        }
        else {
            _states[$(element).attr('data-id')] = true;
            if (!Mana.rwdIsMobile) {
                expand(element, 0);
            }
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
        $('.block-layered-nav .m-collapseable').each(function(index, element) {
            saveState(element);
        });
    });
    $(document).bind('m-ajax-after', function (e, selectors) {
        if (Mana.rwdIsMobile) {
            return;
        }

        $('.block-layered-nav .m-collapseable').each(function (index, element) {
            restoreState(element);
        });
    });
    $(document).bind('m-rwd-wide', function() {
        $('.block-layered-nav .m-collapseable').each(function (index, element) {
            restoreState(element);
        });
    });
    $('.block-layered-nav .m-collapseable').live('click', function() {
        if (Mana.rwdIsMobile) {
            return true;
        }

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

/********************************/
/* dropdown menu in left column */
/********************************/

(function ($) {
    var _selectors = {
        visibleDd: '.block-layered-nav dl dd.m-dropdown-menu.m-popup-filter',
        dt: '.block-layered-nav dl dt.m-dropdown-menu',
        dd: '.block-layered-nav dl dd.m-dropdown-menu'
    };
    function _width(dt, dd) {
        var maxWidth = dd.attr('data-max-width');
        var result = dd.width() > dt.width() ? dd.width() : dt.width();
        return maxWidth ? (result <= maxWidth ? result : maxWidth) : result;
    }

    function _hidePopups() {
        var $popups = $(_selectors.visibleDd);
        if ($popups.length) {
            $popups.each(function () {
                var dd = $(this);
                var dt = $(this).prev();
                dd
                    .removeClass('m-popup-filter')
                    .addClass('hidden');
                dt
                    .removeClass('m-popup-filter');
            });
            return true;
        }
        else {
            return false;
        }
    }

    $(_selectors.dt)
        .live('click', function () {
            if (Mana.rwdIsMobile) {
                return true;
            }

            if (!_hidePopups()) {
                var dt = $(this);
                var dd = $(this).next();
                if (dd.hasClass('hidden')) {
                    dd
                        .removeClass('hidden')
                        .offset({
                            top: dt.offset().top + dt.outerHeight(),
                            left: dt.offset().left
                        })
                        .width(_width(dt, dd))
                        .addClass('m-popup-filter');
                    dt
                        .addClass('m-popup-filter');
                }
                return false;
            }
        });
    $(document).click(function () {
        _hidePopups();
    });

    $(function () {
        $(_selectors.dd).addClass('hidden');
    });

    $(document).bind('m-ajax-after', function () {
        $(_selectors.dd).addClass('hidden');
    });

})(jQuery);