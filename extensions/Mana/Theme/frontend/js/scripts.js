/**
 * @category    Mana
 * @package     Mana_Theme
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

//region tree helper functions
;var Mana = Mana || {};
(function ($, Mana) {
    Mana.Theme = Mana.Theme || {};
    Mana.Theme.tree = function (options) {
        //region preparing options with default values
        var _options = $.extend({
            // options
            optionsSelector:'#tree',
            treeSelector:'.tree',
            itemSelector:'.tree-item li>a',
            textSelector:'.tree-item lia>a>span',
            subtreeSelector:'ul',

            classPrefix:'',
            animatingClass:'animating',
            expandedClass:'expanded',
            collapsedClass:'collapsed',
            leafClass:'leaf',

            slideUpSpeed:'fast',
            slideDownSpeed:'fast',

            treeIdGetter:function (tree) {
                return tree[0].id;
            },
            itemIdGetter:function (item) {
                return item[0].id;
            }
        }, options);
        if (_options.classPrefix) {
            for (var option in _options) {
                //noinspection JSUnfilteredForInLoop
                if (option.indexOf('Class') === option.length - 'Class'.length) {
                    //noinspection JSUnfilteredForInLoop
                    _options[option] = _options.classPrefix + _options[option];
                }
            }
        }
        //endregion
        var _isCollapsedByDefault = false;

        function _collapse(item, showEffect) {
            var li = item.parent();
            if (!li.hasClass(_options.animatingClass)) {
                if (showEffect) {
                    _saveState(item, true);
                    li.addClass(_options.animatingClass);
                    li.children(_options.subtreeSelector).slideUp(_options.slideUpSpeed, function () {
                        li
                            .removeClass(_options.animatingClass)
                            .removeClass(_options.expandedClass)
                            .addClass(_options.collapsedClass);
                    });
                }
                else {
                    li.children(_options.subtreeSelector).hide();
                    li
                        .removeClass(_options.expandedClass)
                        .addClass(_options.collapsedClass);
                }
            }
        }

        function _expand(item, showEffect) {
            var li = item.parent();
            if (!li.hasClass(_options.animatingClass)) {
                if (showEffect) {
                    _saveState(item, false);
                    li.addClass(_options.animatingClass);
                    li.children(_options.subtreeSelector).slideDown(_options.slideDownSpeed, function () {
                        li
                            .removeClass(_options.animatingClass)
                            .removeClass(_options.collapsedClass)
                            .addClass(_options.expandedClass);
                    });
                }
                else {
                    li.children(_options.subtreeSelector).show();
                    li
                        .removeClass(_options.collapsedClass)
                        .addClass(_options.expandedClass);
                }
            }
        }

        var _state = {};

        function _saveState(item, liState) {
            var tree = item.parents(_options.treeSelector);
            if (tree.length) {
                var treeId = _options.treeIdGetter(tree);
                var itemId = _options.itemIdGetter(item);
                var isCollapsed = liState;
                if (_isCollapsedByDefault) {
                    isCollapsed = !isCollapsed;
                }

                if (!_state[treeId]) {
                    _state[treeId] = {};
                }
                _state[treeId][itemId] = isCollapsed ? 1 : 0;
                if ($.options && $.options(_options.optionsSelector) && $.options(_options.optionsSelector).url) {
                    $.post($.options(_options.optionsSelector).url, {selector:_options.optionsSelector, state:_state});
                }
            }
        }

        function _loadState() {
            _isCollapsedByDefault = $.options && $.options(_options.optionsSelector) && $.options(_options.optionsSelector).collapsedByDefault;
            if (!_state.length && $.options && $.options(_options.optionsSelector) && $.options(_options.optionsSelector).state) {
                _state = $.options(_options.optionsSelector).state;
            }
            $(_options.itemSelector).each(function () {
                var item = $(this);
                var tree = item.parents(_options.treeSelector);
                if (tree.length) {
                    var li = item.parent();
                    if (li.children(_options.subtreeSelector).length) {
                        var treeId = _options.treeIdGetter(tree);
                        var itemId = _options.itemIdGetter(item);

                        var isCollapsed = _state[treeId] && _state[treeId][itemId] == 1;
                        if (_isCollapsedByDefault) {
                            isCollapsed = !isCollapsed;
                        }

                        if (isCollapsed) {
                            _collapse($(this), false);
                        }
                        else {
                            _expand($(this), false);
                        }
                    }
                    else {
                        li.addClass(_options.leafClass);
                    }
                }
            });
        }


        $(function () {
            _loadState();
            $(document).on('click', _options.itemSelector, function () {
                if ($(this).parent().hasClass(_options.collapsedClass)) {
                    _expand($(this), true);
                    return false;
                }
                else if ($(this).parent().hasClass(_options.expandedClass)) {
                    _collapse($(this), true);
                    return false;
                }
            });
            $(document).on('click', _options.textSelector, function () {
                setLocation($(this).parent()[0].href);
                return false;
            });
        });
        $(document).bind('m-ajax-after', function (e, selectors, productsClicked) {
            _loadState();
        });
    };

    Mana.Theme.beautifySelects = function(options) {
        var _options = $.extend({
            uiRevealingElementSelector:'button.button, .step-title'
        }, options);
        function _doBeautifySelects() {
            // ignore Opera
            if ($.browser.opera || $.browser.msie && parseInt($.browser.version) < 9) {
                return;
            }

            $('select[multiple!=multiple]').each(function () {
                // initialize only once
                var width;
                if ($.browser.msie && parseInt($.browser.version, 10) <= 8) {
                    width = $(this).width() - 6;
                }
                else {
                    width = $(this).width() - 14;
                }
                var height = $(this).height();
                if (!$(this).parent().hasClass('m-select')) {
                    // fetch initial text to be displayed
                    var title = $(this).attr('title');
                    if ($('option:selected', this).val() != '') {
                        title = $('option:selected', this).text();
                    }
                    else if ($('option', this).length){
                        title = $('option:first', this).text();
                    }

                    // change markup
                    $(this)
                        .wrap('<span class="m-select" />')
                        .after('<span style="width: ' + width + 'px; height: ' + height + 'px;' +
                            ($(this).is(':visible') ? '' : ' display: none;') + '">' + title + '</span>')
                        .parent().show();
                    //.css({ width: $(this).width() + 'px', height: $(this).height() + 'px' });
                }
                else {
                    $(this).next().css({ width:width + 'px', height:height + 'px' });
                    if ($(this).is(':visible')) {
                        $(this).next().show();
                    }
                    else {
                        $(this).next().hide();
                    }
                }

            });
        }

        $(function() {
            _doBeautifySelects();
            _updateDisabled();

            // update text after selection is changed
            $(document).on('change', 'select', function () {
                // update disabled status
                _updateDisabled();

                // do not process events of uninitialized selects
                if (!$(this).parent().hasClass('m-select')) {
                    return;
                }

                // do update text
                val = $('option:selected', this).text();
                $(this).next().text(val);

                _doBeautifySelects();
            });
        });

        function _updateDisabled() {
            $('select').each(function(elementIndex, element) {
                if ($(this).attr('disabled') == 'disabled') {
                    $(this).parent().addClass('disabled');
                }
                else {
                    $(this).parent().removeClass('disabled');
                }
            });
        }
        $(document).bind('m-ajax-after', function (e, selectors) {
            _doBeautifySelects();
        });

        Ajax.Responders.register({onComplete:_doBeautifySelects });
        $(_options.uiRevealingElementSelector).click(_doBeautifySelects);
    };

})(jQuery, Mana);
//endregion

