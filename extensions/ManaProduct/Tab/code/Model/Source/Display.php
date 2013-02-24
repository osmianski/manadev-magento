<?php
/**
 * @category    Mana
 * @package     ManaProduct_Tab
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaProduct_Tab_Model_Source_Display extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        /* @var $t ManaProduct_Tab_Helper_Data */ $t = Mage::helper(strtolower('ManaProduct_Tab'));
		return array(
            array('value' => 'hide', 'label' => $t->__('Hide')),
            array('value' => 'before', 'label' => $t->__('Show Before Tabs')),
            array('value' => 'tab', 'label' => $t->__('Show As Tab')),
            array('value' => 'after', 'label' => $t->__('Show After Tabs')),
        );
    }
}