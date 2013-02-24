<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterPositioning
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_FilterPositioning_Model_Source_Category extends Mana_Core_Model_Source_Abstract {
	protected function _getAllOptions() {
	    /* @var $t ManaPro_FilterPositioning_Helper_Data */ $t = Mage::helper(strtolower('ManaPro_FilterPositioning'));
		return array(
            array('value' => 'none', 'label' => $t->__('Hide')),
			array('value' => 'left', 'label' => $t->__('In Left Column')),
            array('value' => 'right', 'label' => $t->__('In Right Column')),
        );
	}
}