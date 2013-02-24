<?php
/**
 * @category    Mana
 * @package     Mana_Ajax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

class Mana_Ajax_Model_Source_Mode extends Mana_Core_Model_Source_Abstract {
	protected function _getAllOptions() {
		return array(
            array('value' => Mana_Ajax_Model_Mode::OFF, 'label' => Mage::helper('mana_ajax')->__('No')),
            array('value' => Mana_Ajax_Model_Mode::ON_FOR_ALL, 'label' => Mage::helper('mana_ajax')->__('Yes')),
            array('value' => Mana_Ajax_Model_Mode::ON_FOR_USERS, 'label' => Mage::helper('mana_ajax')->__('Yes for Users, No for Search Bots (Listed Below)')),
        );
	}
}