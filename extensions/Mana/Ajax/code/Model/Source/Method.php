<?php
/**
 * @category    Mana
 * @package     Mana_Ajax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

class Mana_Ajax_Model_Source_Method extends Mana_Core_Model_Source_Abstract {
	protected function _getAllOptions() {
		return array(
            array('value' => Mana_Ajax_Model_Method::MARK_WITH_CSS_CLASS, 'label' => Mage::helper('mana_ajax')->__('Mark with CSS class')),
            array('value' => Mana_Ajax_Model_Method::WRAP_INTO_CONTAINER, 'label' => Mage::helper('mana_ajax')->__('Wrap into HTML element')),
        );
	}
}