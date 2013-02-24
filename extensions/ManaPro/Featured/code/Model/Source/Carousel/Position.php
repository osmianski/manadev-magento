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
class ManaPro_Featured_Model_Source_Carousel_Position extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 'top', 'label' => Mage::helper('manapro_featured')->__('Top')),
            array('value' => 'bottom', 'label' => Mage::helper('manapro_featured')->__('Bottom')),
        );
    }
}