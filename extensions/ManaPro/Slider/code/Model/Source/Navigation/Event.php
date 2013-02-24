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
class ManaPro_Slider_Model_Source_Navigation_Event extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 'mouseover', 'label' => Mage::helper('manapro_slider')->__('Mouse Over')),
            array('value' => 'click', 'label' => Mage::helper('manapro_slider')->__('Mouse Click')),
        );
    }
}