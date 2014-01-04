<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSuperSlider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterSuperSlider_Model_Source_MobileStyle extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        /* @var $t ManaPro_FilterSuperSlider_Helper_Data */ $t = Mage::helper(strtolower('ManaPro_FilterSuperSlider'));
        return array(
            array('value' => '', 'label' => $t->__('Same as desktop')),
            array('value' => 'm-hide-slider', 'label' => $t->__('Hide slider; Leave only input fields/labels')),
        );
    }
}