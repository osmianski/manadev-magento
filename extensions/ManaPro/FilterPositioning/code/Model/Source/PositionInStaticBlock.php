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
class ManaPro_FilterPositioning_Model_Source_PositionInStaticBlock extends Mana_Core_Model_Source_Abstract {
	protected function _getAllOptions() {
	    /* @var $t ManaPro_FilterPositioning_Helper_Data */ $t = Mage::helper(strtolower('ManaPro_FilterPositioning'));
		return array(
            array('value' => '', 'label' => $t->__('Before static block')),
			array('value' => 'after', 'label' => $t->__('After static block')),
        );
	}
}