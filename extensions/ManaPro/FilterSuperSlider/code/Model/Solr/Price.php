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
class ManaPro_FilterSuperSlider_Model_Solr_Price extends Mana_Filters_Model_Solr_Price {
    /**
     * Prepare text of item label
     *
     * @param   int $range
     * @param   float $value
     * @return  string
     */
    protected function _renderItemLabel($range, $value) {
        $range = $this->_getResource()->getPriceRange($value, $range);
    	/* @var $helper ManaPro_FilterSuperSlider_Helper_Data */ $helper = Mage::helper(strtolower('ManaPro_FilterSuperSlider'));
        $fromPrice  = $helper->formatNumber($range['from'], $this->getFilterOptions());
        $toPrice    = $helper->formatNumber($range['to'], $this->getFilterOptions());
        return Mage::helper('catalog')->__('%s - %s', $fromPrice, $toPrice);
    }
    public function getExistingValues() {
        $result = array();
        foreach ($this->_getResource()->getExistingValues($this) as $value) {
            $result[] = round($value);
        }
        return array_values(array_unique($result));
    }
    public function getDecimalDigits() {
        return $this->getFilterOptions()->getSliderDecimalDigits();
    }
    protected function _ceil($value) {
        if ($precision = $this->getDecimalDigits()) {
            $result = round($value, $precision);
            if ($result < $value) {
                $result +=  pow(0.1, $precision);
            }

            return $result;
        }
        else {
            return ceil($value);
        }
    }
}