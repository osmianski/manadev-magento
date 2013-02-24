/**
 * @category    Mana
 * @package     Mana_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
;(function($){
    function _isTemplateUsed(template) {
        var result = false;
        _getTemplate().each(function() {
            if ($(this).val() == template) {
                result = true;
            }
        });
        return result;
    }
    function _getType() {
        var result = $('#type');
        if (!result.length) {
            result = $('#select_widget_type');
        }
        return result;
    }

    function _getTemplate() {
        var result = $('.block_template select, .block_template_cms select');
        if (!result.length) {
            result = $('select[name="parameters[template]"]');
        }
        if (!result.length) {
            result = $('select[name="parameters[template_cms]"]');
        }
        return result;
    }

    function _showOrHideColumnCountParameter() {
        var columnCountInput = $('input[name="parameters[column_count]"]');
        if (columnCountInput.length) {
            var tr = columnCountInput.parent().parent();
            switch (_getType().val()) {
                case 'reports/product_widget_compared':
                    if (_isTemplateUsed('reports/widget/compared/content/compared_grid.phtml')) {
                        tr.show();
                    }
                    else {
                        tr.hide();
                    }
                    break;
                case 'reports/product_widget_viewed':
                    if (_isTemplateUsed('reports/widget/viewed/content/viewed_grid.phtml')) {
                        tr.show();
                    }
                    else {
                        tr.hide();
                    }
                    break;
                case 'catalog/product_widget_new':
                    if (_isTemplateUsed('catalog/product/widget/new/content/new_grid.phtml')) {
                        tr.show();
                    }
                    else {
                        tr.hide();
                    }
                    break;
                case 'manapage_bestseller/widget':
                    if (_isTemplateUsed('manapage/bestseller/grid.phtml')) {
                        tr.show();
                    }
                    else {
                        tr.hide();
                    }
                    break;
                case 'manapage_attributeoption/widget':
                    if (_isTemplateUsed('manapage/attributeoption/grid.phtml')) {
                        tr.show();
                    }
                    else {
                        tr.hide();
                    }
                    break;
                case 'manapage_sale/widget':
                    if (_isTemplateUsed('manapage/sale/grid.phtml')) {
                        tr.show();
                    }
                    else {
                        tr.hide();
                    }
                    break;
            }
        }
    }
    $(function() {
        _showOrHideColumnCountParameter();
        $('.block_reference select, .block_template_cms select, .block_template select, #select_widget_type, ' +
          'select[name="parameters[template]"], select[name="parameters[template_cms]"]')
            .live('change', _showOrHideColumnCountParameter);
        Ajax.Responders.register({onComplete:_showOrHideColumnCountParameter });
    });
})(jQuery);