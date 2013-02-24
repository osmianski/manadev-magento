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
class ManaPro_Slider_Model_Source_Side extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => 'left', 'label' => Mage::helper('manapro_slider')->__('Left')),
            array('value' => 'top', 'label' => Mage::helper('manapro_slider')->__('Top')),
            array('value' => 'right', 'label' => Mage::helper('manapro_slider')->__('Right')),
            array('value' => 'bottom', 'label' => Mage::helper('manapro_slider')->__('Bottom')),
        );
    }
}