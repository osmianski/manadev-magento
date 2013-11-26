<?php
/**
 * @category    Mana
 * @package     Mana_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class Mana_Theme_Model_Source_Position_Related extends Mana_Core_Model_Source_Abstract {
	protected function _getAllOptions() {
	    /* @var $t ManaPro_FilterPositioning_Helper_Data */ $t = Mage::helper(strtolower('Mana_Theme'));
		return array(
            array('value' => 'none', 'label' => $t->__('Hide')),
			array('value' => 'left', 'label' => $t->__('In Left Column')),
            array('value' => 'right', 'label' => $t->__('In Right Column')),
            array('value' => 'inside_product', 'label' => $t->__('Product Content')),
        );
	}
}