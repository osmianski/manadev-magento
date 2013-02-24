/**
 * @category    Mana
 * @package     ManaPro_Video
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
;(function($){
    $('#mVideoGrid .filter-actions .m-add').live('click', function() {
        $.gridAction('mVideoGrid', 'add');
    });
    $('#mVideoGrid .filter-actions .m-remove').live('click', function () {
        $.gridAction('mVideoGrid', 'remove');
    });
})(jQuery);