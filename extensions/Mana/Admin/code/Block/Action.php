<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Block_Action extends Mage_Adminhtml_Block_Widget_Button {
    public function getType() {
        if ($type = $this->getData('type')) {
            if (strpos($type, '/') === false) {
                return $type;
            }
            else {
                return ($type = $this->getData('action_type')) ? $type : 'button';
            }
        }
        else {
            return 'button';
        }
    }


    protected function _prepareLayout() {
        parent::_prepareLayout();

        /* @var $layoutHelper Mana_Core_Helper_Layout */
        $layoutHelper = Mage::helper('mana_core/layout');
        $layoutHelper->delayPrepareLayout($this);

        return $this;

    }

    public function delayedPrepareLayout() {
        $this->addToParentGroup('actions');

        // create client-side block
        $this->_prepareClientSideBlock();

        return $this;
    }

    protected function _prepareClientSideBlock() {
        $this->setMClientSideBlock(array(
            'type' => 'Mana/Admin/Action',
            'self_contained'=> true
        ));

        return $this;
    }

    protected function _toHtml() {
        /* @var $js Mana_Core_Helper_Js */
        $js = Mage::helper('mana_core/js');

        $info = $js->parseClientSideBlockInfo($this);

        $style = $this->getStyle();
        if ($style && $info['style']) {
            $style .= ' ';
        }
        $style  .= $info['style'];

        $html = $this->getBeforeHtml() . '<button '
            . ($this->getData('id') ? ' id="' . $this->getData('id') . '"' : '')
            . ($this->getElementName() ? ' name="' . $this->getElementName() . '"' : '')
            . ' type="' . $this->getType() . '"'
            . ' class="scalable ' . $this->getClass() . ' '.$info['class'].'"'
            . ($style ? ' style="' . $style . '"' : '')
            . ($this->getValue() ? ' value="' . $this->getValue() . '"' : '')
            . ($this->getDisabled() ? ' disabled="disabled"' : '')
            . $info['attribute_html']
            . '><span>' . $this->getLabel() . '</span></button>' . $this->getAfterHtml();

        return $html;
    }
}