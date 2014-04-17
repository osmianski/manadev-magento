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
class ManaPro_FilterSuperSlider_Model_Source_MinSlider extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        /* @var $t ManaPro_FilterSuperSlider_Helper_Data */ $t = Mage::helper(strtolower('ManaPro_FilterSuperSlider'));

        /* @var $collection Mana_Filters_Resource_Filter2_Collection */
        if (Mage::helper('mana_admin')->isGlobal()) {
			$collection = Mage::getResourceModel('mana_filters/filter2_collection');
		}
		else {
			$collection = Mage::getResourceModel('mana_filters/filter2_store_collection');
			$collection->addStoreFilter(Mage::helper('mana_admin')->getStore());
		}
		$collection->addColumnToSelect(array('code', 'name'));
        $collection->getSelect()->where("main_table.min_max_slider_role = 'min'");


        $result = array(array('value' => '', 'label' => $t->__(' -- Select --')));
        foreach ($collection as $filter) {
            /* @var $filter Mana_Filters_Model_Filter2 */
            $result[] = array(
                'value' => $filter->getCode(),
                'label' => $t->__("%s (code '%s')", $filter->getData('name'), $filter->getCode())
            );
        }

        return $result;
    }
}