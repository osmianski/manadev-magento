/**
 * @category    Mana
 * @package     ManaPro_Video
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
;(function($){
    $(document).on('click', '#mVideoGrid .filter-actions .m-add', function() {
        $.gridAction('mVideoGrid', 'add');
    });
    $(document).on('click', '#mVideoGrid .filter-actions .m-remove', function () {
        $.gridAction('mVideoGrid', 'remove');
    });
})(jQuery);