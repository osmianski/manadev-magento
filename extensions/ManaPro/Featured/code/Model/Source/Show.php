<?php
/**
 * @category    Mana
 * @package     ManaPro_Featured
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Featured_Model_Source_Show extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 'all', 'label' => Mage::helper('manapro_featured')->__('All Featured Products')),
            array('value' => 'specified', 'label' => Mage::helper('manapro_featured')->__('Specified Number Of Products')),
        );
    }
}