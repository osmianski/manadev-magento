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
class ManaPro_FilterSuperSlider_Model_Decimal extends Mana_Filters_Model_Filter_Decimal {
    protected function _renderItemLabel($range, $value) {
        $range = $this->_getResource()->getRange($value, $range);
        /* @var $helper ManaPro_FilterSuperSlider_Helper_Data */ $helper = Mage::helper(strtolower('ManaPro_FilterSuperSlider'));
        $fromPrice  = $helper->formatNumber($range['from'], $this->getFilterOptions());
        $toPrice    = $helper->formatNumber($range['to'], $this->getFilterOptions());
        return Mage::helper('catalog')->__('%s - %s', $fromPrice, $toPrice);
    }
    public function getExistingValues() {
        $result = array();
        foreach ($this->_getResource()->getExistingValues($this) as $value) {
            $result[] = $value;
        }
        return array_values(array_unique($result));
    }

    public function isFilterAppliedWhenCounting($modelToBeApplied) {
        if ($this->_getIsFilterable() != 2) {
            return $modelToBeApplied != $this &&
                !in_array($modelToBeApplied->getFilterOptions()->getDisplay(), array('slider', 'range', 'min_max_slider'));
        }
        else {
            return false;
        }
    }

    public function getItemsCount() {
        return $this->getMaxValue() > $this->getMinValue() ? 2 : 0;
    }

}