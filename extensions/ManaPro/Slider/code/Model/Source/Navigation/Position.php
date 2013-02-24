<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Slider_Model_Source_Navigation_Position extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 'top', 'label' => Mage::helper('manapro_slider')->__('Top')),
            array('value' => 'bottom', 'label' => Mage::helper('manapro_slider')->__('Bottom')),
        );
    }
}