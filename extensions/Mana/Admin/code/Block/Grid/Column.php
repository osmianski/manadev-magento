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
}