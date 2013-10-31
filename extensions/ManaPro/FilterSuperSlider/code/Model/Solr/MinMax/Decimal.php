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
class ManaPro_FilterSuperSlider_Model_Solr_MinMax_Decimal extends ManaPro_FilterSuperSlider_Model_Solr_Decimal {
    protected $_maxFilter;
    protected $_maxFilterInitialized;

    public function getMaxFilter() {
        if (!$this->_maxFilterInitialized) {
            $maxFilter = null;

            /* @var $minOptions Mana_Filters_Model_Filter2_Store */
            $minOptions = $this->getData('filter_options');

            /* @var $query Mana_Filters_Model_Query */
            $query = $this->getData('query');

            if ($minOptions->getData('min_max_slider_role') == 'min') {
                foreach ($query->getFilters() as $filterData) {
                    $filter = $filterData['model'];
                    /* @var $filter ManaPro_FilterSuperSlider_Model_MinMax_Decimal */

                    /* @var $filterOptions Mana_Filters_Model_Filter2_Store */
                    $filterOptions = $filter->getData('filter_options');

                    if ($filterOptions->getData('display') == $minOptions->getData('display') &&
                        $filterOptions->getData('min_slider_code') == $minOptions->getData('code') &&
                        $filterOptions->getData('min_max_slider_role') == 'max')
                    {
                        $maxFilter = $filter;
                        break;
                    }
                }
            }
            $this->_maxFilter = $maxFilter;
            $this->_maxFilterInitialized = true;
        }
        return $this->_maxFilter;
    }

    protected function _calculateMinMax() {
        if (!$this->_isMinMaxCalculated) {
            if ($maxFilter = $this->getMaxFilter()) {
                $this->_minMax = $this->getDecimalMinMax();
                if (!empty($this->_minMax['hasNoResults'])) {
                    unset($this->_minMax['hasNoResults']);
                    $this->_hasNoResults = true;
                }
                $minMax = $maxFilter->getDecimalMinMax();
                if (!($this->_minMax['min'] == 0 && $this->_minMax['max'] == 0) &&
                    !($minMax['min'] == 0 && $minMax['max'] == 0))
                {
                    if ($this->_minMax['min'] > $minMax['min']) {
                        $this->_minMax['min'] = $minMax['min'];
                    }
                    if ($this->_minMax['max'] < $minMax['max']) {
                        $this->_minMax['max'] = $minMax['max'];
                    }
                }
            }
            else {
                $this->_minMax = array('min' => 0, 'max' => 0);
            }
            $this->_isMinMaxCalculated = true;
        }

        return $this->_minMax;
    }

    protected function _getItemsData() {
        if ($this->getMaxFilter()) {
            return parent::_getItemsData();
        }
        else {
            return array();
        }
    }

    public function applyToCollection($collection)
    {
        $attributeCode     = $this->getAttributeModel()->getAttributeCode();
        $field             = 'attr_decimal_'. $attributeCode;
        $maxField = 'attr_decimal_' . $this->getMaxFilter()->getAttributeModel()->getAttributeCode();

        $fq = array();
        foreach ($this->getMSelectedValues() as $selection) {
            if (strpos($selection, ',') !== false) {
                list($index, $range) = explode(',', $selection);
                $range = $this->_getResource()->getRange($index, $range);
                $fq[] = array(
                    'from' => $range['from'],
                    'to'   => $range['to'] - ($this->isUpperBoundInclusive() ? 0 : 0.001),
                );
            }
        }

        $collection->addFqFilter(array('min-max:'.$field.','. $maxField => $fq));
    }

    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Filters_Helper_Data
     */
    public function filterHelper() {
        return Mage::helper('mana_filters');
    }
    #endregion
}