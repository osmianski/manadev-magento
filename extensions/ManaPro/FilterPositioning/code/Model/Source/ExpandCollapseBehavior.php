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
class ManaPro_FilterPositioning_Model_Source_ExpandCollapseBehavior extends Mana_Core_Model_Source_Abstract {
	protected function _getAllOptions() {
	    /* @var $t ManaPro_FilterPositioning_Helper_Data */ $t = Mage::helper(strtolower('ManaPro_FilterPositioning'));
		return array(
            array('value' => 'initially-collapsed', 'label' => $t->__('Initially collapsed; expanded manually')),
			array('value' => 'initially-expanded', 'label' => $t->__('Initially expanded; collapsed manually')),
            array('value' => 'accordion', 'label' => $t->__('Accordion: one filter expanded at a time')),
        );
	}
}