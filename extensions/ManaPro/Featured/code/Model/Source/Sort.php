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
class ManaPro_Featured_Model_Source_Sort extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        $result = array();
        foreach (Mage::getSingleton('catalog/config')->getAttributeUsedForSortByArray() as $key => $value) {
            $result[] = array('value' => $key.'_asc', 'label' => Mage::helper('manapro_featured')->__('%s Ascending', $value));
            $result[] = array('value' => $key . '_desc', 'label' => Mage::helper('manapro_featured')->__('%s Descending', $value));
        }
        $result[] = array('value' => 'random', 'label' => Mage::helper('manapro_featured')->__('Randomly'));
        return $result;
    }
}