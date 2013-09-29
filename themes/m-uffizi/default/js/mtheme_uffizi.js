/**
 * @category    Mana
 * @package     ManaTheme_Ufizzi
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
 
(function ($, Mana) {
    $(function() {
        function _getItemIdFromNavClass(item) {
            var result = '';
            var li = item.parent();
            $.each(li.attr('class').split(' '), function (classIndex, className) {
                if (className.indexOf('nav-') === 0) {
                    result = this;
                }
            });
            return result;
        }

        //region left and right category tree
        Mana.Theme.tree({
            classPrefix:'cf-',
            optionsSelector:'#cf-left-nav-tree',
            treeSelector:'#left-nav',
            itemSelector:'#left-nav li>a',
            textSelector:'#left-nav li>a>span',
            itemIdGetter:_getItemIdFromNavClass
        });
        Mana.Theme.tree({
            classPrefix:'cf-',
            optionsSelector:'#cf-right-nav-tree',
            treeSelector:'#right-nav',
            itemSelector:'#right-nav li>a',
            textSelector:'#right-nav li>a>span',
            itemIdGetter:_getItemIdFromNavClass
        });

        // HTML SELECTs wrapped into additional styled DIVs
        Mana.Theme.beautifySelects();

        //region cart content show-hide when mouse roll over it
        Mana.Theme.slidingCart();
    });

})(jQuery, Mana);
//endregion

