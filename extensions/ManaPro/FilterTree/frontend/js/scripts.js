/**
 * @category    Mana
 * @package     ManaPro_FilterTree
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

;(function($) {
    var _isCollapsedByDefault = false;
    var _expandSelected = false;

    function _collapse(item, showEffect) {
        var li = item.parent();
        if (!li.hasClass('m-animating')) {
            if (showEffect) {
                _saveState(item, true);
                li.addClass('m-animating');
                li.children('ul').slideUp('fast', function () {
                    li.removeClass('m-animating').removeClass('m-expanded').addClass('m-collapsed');
                });
            }
            else {
                li.children('ul').hide();
                li.removeClass('m-expanded').addClass('m-collapsed');
            }
        }
    }

    function _expand(item, showEffect) {
        var li = item.parent();
        if (!li.hasClass('m-animating')) {
            if (showEffect) {
                _saveState(item, false);
                li.addClass('m-animating');
                li.children('ul').slideDown('fast', function () {
                    li.removeClass('m-animating').removeClass('m-collapsed').addClass('m-expanded');
                });
            }
            else {
                li.children('ul').show();
                li.removeClass('m-collapsed').addClass('m-expanded');
            }
        }
    }

    var _state = {};
    function _saveState(item, liState) {
        var tree = item.parents('.m-tree');
        if (tree.length) {
            var treeId = tree[0].id;
            var itemId = item[0].id;
            var isCollapsed = liState;
            if (_isCollapsedByDefault) {
                isCollapsed = !isCollapsed;
            }

            if (!_state[treeId]) {
                _state[treeId] = {};
            }
            _state[treeId][itemId] = isCollapsed ? 1: 0;
            $.post($.options('#m-tree').url, {state:_state});
        }
    }

    function _loadState() {
        _isCollapsedByDefault = $.options && $.options('#m-tree') && $.options('#m-tree').collapsedByDefault;
        _expandSelected = $.options && $.options('#m-tree') && $.options('#m-tree').expandSelected;
        if (!_state.length && $.options && $.options('#m-tree') && $.options('#m-tree').state) {
            _state = $.options('#m-tree').state;
        }
        $('.m-tree-item').each(function () {
            var item = $(this);
            var tree = item.parents('.m-tree');
            if (tree.length) {
                var li = item.parent();
                if (li.children('ul').length) {
                    var treeId = tree[0].id;
                    var itemId = item[0].id;
                    var isCollapsed;

                    if (li.find('ul .m-selected-filter-item').length) {
                        isCollapsed = false;
                    }
                    else if (li.find('.m-selected-filter-item').length && _expandSelected) {
                        isCollapsed = false;
                    }
                    else {
                        isCollapsed = _state[treeId] && _state[treeId][itemId] == 1;
                        if (_isCollapsedByDefault) {
                            isCollapsed = !isCollapsed;
                        }
                    }

                    if (isCollapsed) {
                        _collapse($(this), false);
                    }
                    else {
                        _expand($(this), false);
                    }
                }
                else {
                    li.addClass('m-leaf');
                }
            }
        });
    }


    $(function () {
        _loadState();
        $('.m-tree-item').live('click', function (e) {
            if ($(e.target).prop("tagName").toLowerCase() == 'a') {
                return true;
            }
            if ($(this).parent().hasClass('m-collapsed')) {
                _expand($(this), true);
                return false;
            }
            else if ($(this).parent().hasClass('m-expanded')) {
                _collapse($(this), true);
                return false;
            }
        });
    });
    $(document).bind('m-ajax-after', function (e, selectors, productsClicked) {
        _loadState();
    });
})(jQuery);