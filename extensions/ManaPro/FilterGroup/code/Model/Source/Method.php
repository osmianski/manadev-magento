<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterGroup
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterGroup_Model_Source_Method extends Mana_Core_Model_Source_Abstract {
	protected function _getAllOptions() {
		return array(
            array('value' => '', 'label' => Mage::helper('manapro_filtergroup')->__("Don't group filters")),
            array('value' => 'attribute_group', 'label' => Mage::helper('manapro_filtergroup')->__('Attribute groups')),
        );
	}
}