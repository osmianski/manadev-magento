<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterExpandCollapse
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterExpandCollapse_Model_Source_Method extends Mana_Core_Model_Source_Abstract {
	protected function _getAllOptions() {
		return array(
            array('value' => '', 'label' => Mage::helper('manapro_filterexpandcollapse')->__("Expanded, not collapseable")),
            array('value' => 'collapse', 'label' => Mage::helper('manapro_filterexpandcollapse')->__("Collapseable, initially collapsed")),
            array('value' => 'expand', 'label' => Mage::helper('manapro_filterexpandcollapse')->__("Collapseable, initially expanded")),
            array('value' => 'dropdown', 'label' => Mage::helper('manapro_filterexpandcollapse')->__("Dropdown")),
        );
	}
}