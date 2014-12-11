<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class ManaPro_FilterSeoLinks_Model_Condition  {
    /**
     * @param Mage_Catalog_Model_Layer $layerModel
     * @return bool
     */
    abstract public function detect($layerModel);

    /**
     * @param Mage_Catalog_Model_Layer $layerModel
     * @return int
     */
    protected function _countOptionsInAllFilters($layerModel) {
        if (!$layerModel->hasData('m_count_options_in_all_filters')) {
            $layerModel->setData('m_count_options_in_all_filters', count($layerModel->getState()->getFilters()));
        }
        return $layerModel->getData('m_count_options_in_all_filters');
    }

    /**
     * @param Mage_Catalog_Model_Layer $layerModel
     * @return int
     */
    protected function _countFilters($layerModel) {
        if (!$layerModel->hasData('m_count_filters')) {
            $filters = array();
            foreach ($layerModel->getState()->getFilters() as $item) {
                /* @var $item Mana_Filters_Model_Item */
                $add = true;
                foreach ($filters as $filter) {
                    if ($item->getFilter() == $filter) {
                        $add = false;
                        break;
                    }
                }
                if ($add) {
                    $filters[] = $item->getFilter();
                }
            }
            $layerModel->setData('m_count_filters', count($filters));
        }
        return $layerModel->getData('m_count_filters');
    }

    /**
     * @param Mage_Catalog_Model_Layer $layerModel
     * @return int
     */
    protected function _countOptionsInTheSameFilter($layerModel) {
        if (!$layerModel->hasData('m_count_options_in_same_filter')) {
            $filters = array();
            $result = 0;
            foreach ($layerModel->getState()->getFilters() as $item) {
                /* @var $item Mana_Filters_Model_Item */
                $code = $item->getFilter()->getRequestVar();
                if (!isset($filters[$code])) {
                    $filters[$code] = array('code' => $code, 'count' => 0);
                }
                $filters[$code]['count']++;
                if ($result < $filters[$code]['count']) {
                    $result = $filters[$code]['count'];
                }
            }
            $layerModel->setData('m_count_options_in_same_filter', $result);
        }
        return $layerModel->getData('m_count_options_in_same_filter');
    }

    /**
     * @param Mage_Catalog_Model_Layer $layerModel
     * @return bool
     */
    protected function _isSliderApplied($layerModel) {
         if (!$layerModel->hasData('m_is_slider_applied')) {
           $result = false;
           foreach ($layerModel->getState()->getFilters() as $item) {
                /* @var $item Mana_Filters_Model_Item */
                if ($item->getFilter()->getFilterOptions() && in_array($item->getFilter()->getFilterOptions()->getDisplay(),
                    array('slider', 'range', 'min_max_slider')))
                {
                    $result = true;
                    break;
                }
            }
            $layerModel->setData('m_is_slider_applied', $result);
        }
        return $layerModel->getData('m_is_slider_applied');
    }
}