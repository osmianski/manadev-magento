<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterDependent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_FilterDependent module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_FilterDependent_Helper_Data extends Mage_Core_Helper_Abstract {
    /**
     * @param Mana_Filters_Model_Filter2_Store $filter
     * @param Mana_Filters_Resource_Filter2_Store_Collection $collection
     * @return bool
     */
    public function hide($filter, $collection) {
        /* @var $parentFilter Mana_Filters_Model_Filter2_Store */
        if (($id = $filter->getData('depends_on_filter_id')) &&
            ($parentFilter = $this->coreHelper()->collectionFind($collection, 'global_id', $id)))
        {
            return $this->_hideRecursively($parentFilter, $collection);
        }

        return false;
    }

    /**
     * @param Mana_Filters_Model_Filter2_Store $filter
     * @param Mana_Filters_Resource_Filter2_Store_Collection $collection
     * @return bool
     */
    protected function _hideRecursively($filter, $collection) {
        $requestVar = $filter->getData('type') == 'category' ? 'cat' : $filter->getData('code');
        if (!Mage::app()->getRequest()->getParam($requestVar)) {
            return true;
        }

        /* @var $parentFilter Mana_Filters_Model_Filter2_Store */
        if (($id = $filter->getData('depends_on_filter_id')) &&
            ($parentFilter = $this->coreHelper()->collectionFind($collection, 'global_id', $id)))
        {
            return $this->_hideRecursively($parentFilter, $collection);
        }

        return false;
    }

    /**
     * @param array $query
     * @param string $requestVar
     * @return array
     */
    public function removeDependentFiltersFromUrl($query, $requestVar) {
        return $this->_removeDependentFiltersFromUrlRecursively($query, $requestVar,
            $this->layerHelper()->getFilterOptionsCollection());
    }

    /**
     * @param array $query
     * @param string $requestVar
     * @param Mana_Filters_Resource_Filter2_Store_Collection $collection
     * @return array
     */
    protected function _removeDependentFiltersFromUrlRecursively($query, $requestVar, $collection) {
        $code = $requestVar == 'cat' ? 'category' : $requestVar;
        /* @var $parentFilter Mana_Filters_Model_Filter2_Store */
        if ($parentFilter = $this->coreHelper()->collectionFind($collection, 'code', $code)) {
            foreach ($collection as $filter) {
                /* @var $filter Mana_Filters_Model_Filter2_Store */
                if ($filter->getData('depends_on_filter_id') == $parentFilter->getData('global_id')) {
                    $childRequestVar = $filter->getData('type') == 'category' ? 'cat' : $filter->getData('code');
                    $query[$childRequestVar] = null;
                    $query = $this->_removeDependentFiltersFromUrlRecursively($query, $childRequestVar, $collection);
                }
            }
        }
        return $query;
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
    public function layerHelper() {
        return Mage::helper('mana_filters');
    }

    #endregion
}