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
class ManaPro_FilterSuperSlider_Model_Source_Style extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        /* @var $t ManaPro_FilterSuperSlider_Helper_Data */ $t = Mage::helper(strtolower('ManaPro_FilterSuperSlider'));
        return array(
            array('value' => '', 'label' => $t->__('Style 1')),
            array('value' => 'style2', 'label' => $t->__('Style 2')),
            array('value' => 'style3', 'label' => $t->__('Style 3')),
            array('value' => 'style4', 'label' => $t->__('Style 4')),
        );
    }
}