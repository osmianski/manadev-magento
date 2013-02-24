<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterPositioning
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterPositioning_Model_Source_Show_In extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 'left', 'label' => Mage::helper('manapro_filterpositioning')->__('In Left Column')),
            array('value' => 'right', 'label' => Mage::helper('manapro_filterpositioning')->__('In Right Column')),
            array('value' => 'above_products', 'label' => Mage::helper('manapro_filterpositioning')->__('Above Product List')),
        );
    }
    public function toOptionArray() {
        return $this->_getAllOptions();
    }
}