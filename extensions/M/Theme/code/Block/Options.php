<?php
/**
 * @category    Mana
 * @package     M_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class M_Theme_Block_Options extends Mage_Core_Block_Abstract {
    protected $_trees = array();

    protected function _beforeToHtml() {
        /* @var $js Mana_Core_Helper_Js */ $js = Mage::helper('mana_core/js');
        $states = Mage::getSingleton('core/session')->getMTreeStates();
        foreach ($this->_trees as $tree) {
            $selector = $tree['selector'];
            $state = $states && isset($states[$selector]) ? $states[$selector] : null;
            $js->options($selector, array_merge(array(
                'url' => $this->getUrl('m_theme/tree/state'),
                'collapsedByDefault' => true,
            ), !$state ? array() : array(
                'state' => $state
            )));
        }
        return parent::_beforeToHtml();
    }
    public function addTreeState($selector) {
        $this->_trees[$selector] = compact('selector');
    }
}