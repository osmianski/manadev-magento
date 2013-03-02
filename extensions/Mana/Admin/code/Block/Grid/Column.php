<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getTitle()
 * @method string getType()
 * @method string getSourceModel()
 * @method string getSortOrder()
 */
class Mana_Admin_Block_Grid_Column extends Mage_Adminhtml_Block_Widget_Grid_Column {
    protected function _prepareLayout() {
        parent::_prepareLayout();

        /* @var $layoutHelper Mana_Core_Helper_Layout */
        $layoutHelper = Mage::helper('mana_core/layout');
        $layoutHelper->delayPrepareLayout($this);

        return $this;

    }

    public function delayedPrepareLayout() {
        $this->addToParentGroup('columns');

        // create client-side block
        $this->_prepareClientSideBlock();

        return $this;
    }

    protected function _prepareClientSideBlock() {
        $this->setMClientSideBlock(array(
            'type' => 'Mana/Admin/Block/Grid/Column',
            'self_contained' => true
        ));

        return $this;
    }

    public function getHtmlProperty() {
        /* @var $js Mana_Core_Helper_Js */
        $js = Mage::helper('mana_core/js');
        $info = $js->parseClientSideBlockInfo($this);
        $out = parent::getHtmlProperty();

        foreach (array('class', 'style') as $attribute) {
            $value = '';
            if (preg_match('/'. $attribute.'="(.*?)"/i', $out, $matches)) {
                $value = $matches[1];
                $out = preg_replace('/'. $attribute.'=".*?"/i', '', $out);
            }
            if ($value && $info[$attribute]) {
                $value .= ' ';
            }
            $value .= $info[$attribute];
            if ($value) {
                $out .= "$attribute=\"$value\"";
            }
        }

        $out .= $info['attribute_html'];
        return $out;
    }

    protected function _getRendererByType() {
        $type = strtolower($this->getType());
        $renderers = $this->getGrid()->getColumnRenderers();

        if (is_array($renderers) && isset($renderers[$type])) {
            return $renderers[$type];
        }

        if ($this->getOriginalRenderer()) {
            return parent::_getRendererByType();        
        }
            
        switch ($type) {
            case 'date':
                $rendererClass = 'mana_admin/grid_column_date';
                break;
            case 'datetime':
                $rendererClass = 'mana_admin/grid_column_datetime';
                break;
            case 'number':
                $rendererClass = 'mana_admin/grid_column_number';
                break;
            case 'currency':
                $rendererClass = 'mana_admin/grid_column_currency';
                break;
            case 'price':
                $rendererClass = 'mana_admin/grid_column_price';
                break;
            case 'country':
                $rendererClass = 'mana_admin/grid_column_country';
                break;
            case 'concat':
                $rendererClass = 'mana_admin/grid_column_concat';
                break;
            case 'action':
                $rendererClass = 'mana_admin/grid_column_action';
                break;
            case 'options':
                $rendererClass = 'mana_admin/grid_column_options';
                break;
            case 'checkbox':
                $rendererClass = 'mana_admin/grid_column_checkbox';
                break;
            case 'massaction':
                $rendererClass = 'mana_admin/grid_column_massaction';
                break;
            case 'radio':
                $rendererClass = 'mana_admin/grid_column_radio';
                break;
            case 'input':
                $rendererClass = 'mana_admin/grid_column_input';
                break;
            case 'select':
                $rendererClass = 'mana_admin/grid_column_select';
                break;
            case 'text':
                $rendererClass = 'mana_admin/grid_column_longtext';
                break;
            case 'store':
                $rendererClass = 'mana_admin/grid_column_store';
                break;
            case 'wrapline':
                $rendererClass = 'mana_admin/grid_column_wrapline';
                break;
            case 'theme':
                $rendererClass = 'mana_admin/grid_column_theme';
                break;
            default:
                $rendererClass = 'mana_admin/grid_column_text';
                break;
        }

        return $rendererClass;
    }

    public function getRendererClass() {
        return $this->_getRendererByType();
    }
}