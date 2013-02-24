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
class ManaPro_FilterPositioning_Model_Source_State extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => '', 'label' => Mage::helper('manapro_filterpositioning')->__('No')),
            array('value' => 'all', 'label' => Mage::helper('manapro_filterpositioning')->__('Yes, Show All Filters Applied On Whole Page')),
            array('value' => 'this', 'label' => Mage::helper('manapro_filterpositioning')->__('Yes, But Only Filters Applied In This Block')),
        );
    }
    public function toOptionArray() {
        return $this->_getAllOptions();
    }
}