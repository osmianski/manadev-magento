<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAdvanced
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterAdvanced_Model_Source_Expandcollapse extends Mana_Core_Model_Source_Abstract {
	protected function _getAllOptions() {
	    /* @var $t ManaPro_FilterAdvanced_Helper_Data */ $t = Mage::helper(strtolower('ManaPro_FilterAdvanced'));
		return array(
            array('value' => '', 'label' => $t->__("Expanded, not collapseable")),
            array('value' => 'collapse', 'label' => $t->__("Collapseable, initially collapsed")),
            array('value' => 'expand', 'label' => $t->__("Collapseable, initially expanded")),
        );
	}
}