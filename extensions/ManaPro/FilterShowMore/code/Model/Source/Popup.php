<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterShowMore
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterShowMore_Model_Source_Popup extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 'click', 'label' => Mage::helper('manapro_filtershowmore')->__("Mouse Click")),
            array('value' => 'mouseover', 'label' => Mage::helper('manapro_filtershowmore')->__('Mouse Over')),
        );
    }
}