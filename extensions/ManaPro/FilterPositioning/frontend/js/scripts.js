/**
 * @category    Mana
 * @package     ManaPro_FilterPositioning
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
;(function($) {
    function _width(dt, dd) {
        var maxWidth = dd.attr('data-max-width');
        var result = dd.width() > dt.width() ? dd.width() : dt.width();
        return maxWidth ? (result <= maxWidth ? result : maxWidth) : result;
    }

  $('.col-main div.block-layered-nav.m-topmenu dl dt.m-ln')
    .live('mouseover', function() {
        if ($(this).parent().hasClass('m-inline')) {
            return true;
        }

        var dt = $(this);
        var dd = $(this).next();
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
      })
    .live('mouseout', function() {
        if ($(this).parent().hasClass('m-inline')) {
            return true;
        }

        var dt = $(this);
        var dd = $(this).next();
        dd
          .removeClass('m-popup-filter')
          .addClass('hidden');
        dt
          .removeClass('m-popup-filter');
      });
  $('.col-main div.block-layered-nav.m-topmenu dl dd.m-ln')
    .live('mouseover', function() {
        if ($(this).parent().hasClass('m-inline')) {
            return true;
        }

        var dd = $(this);
        var dt = $(this).prev();
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
      })
      .live('mouseout', function() {
        if ($(this).parent().hasClass('m-inline')) {
            return true;
        }

        var dd = $(this);
        var dt = $(this).prev();
        dd
          .removeClass('m-popup-filter')
          .addClass('hidden');
        dt
          .removeClass('m-popup-filter');

      });
    
})(jQuery);