/**
 * @category    Mana
 * @package     ManaPro_FilterTree
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

; // make JS merging easier

Mana.require(['jquery', 'singleton:Mana/Core/Ajax', 'singleton:Mana/Core/Config', 'singleton:Mana/Core/UrlTemplate'],
function ($, ajax, config, urlTemplate) {
    $(function () {
        $(document).on('click', 'a.make_all_categories_anchor_for_tree_filter-action', function () {
            ajax.post(urlTemplate.decodeAttribute(config.getData('filterTree.make_all_categories_anchor_for_tree_filter_action_url')),
                [{name: 'form_key', value: FORM_KEY}], function(response)
            {
                alert(response);
            });
        });
        $(document).on('click', 'a.make_all_categories_anchor_for_tree_filter-message', function () {
            _hideMessage($(this));

            ajax.post(urlTemplate.decodeAttribute(config.getData('filterTree.make_all_categories_anchor_for_tree_filter_message_url')), [
                {name: 'form_key', value: FORM_KEY}
            ]);
        });
    });

    function _hideMessage($a) {
        //noinspection JSCheckFunctionSignatures
        var $li = $a.parent();
        $li.hide();
        //noinspection JSCheckFunctionSignatures
        for (var $parent = $li.parent(); $parent.length && $parent[0].id != 'messages'; $parent = $parent.parent()) {
            if ($parent.children(':visible').length) {
                break;
            }
            $parent.hide();
        }
    }

    function _showMessage($a) {
        //noinspection JSCheckFunctionSignatures
        var $li = $a.parent();
        $li.show();
        //noinspection JSCheckFunctionSignatures
        for (var $parent = $li.parent(); $parent.length && $parent[0].id != 'messages'; $parent = $parent.parent()) {
            $parent.show();
        }
    }

    function _change() {
        var $a = $('a.make_all_categories_anchor_for_tree_filter-message');
        if ($a.length) {
            var $this = $('#mf_general_display');
            if ($this.val() == 'tree') {
                _showMessage($a);
            }
            else {
                _hideMessage($a);
            }
        }
    }

    $(_change);
    $(document).on('change', '#mf_general_display', _change);
});
