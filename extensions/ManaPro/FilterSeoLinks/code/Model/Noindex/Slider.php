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
class ManaPro_FilterSeoLinks_Model_Noindex_Slider {
    public function detect($layerModel) {
        $filter = null;
        $result = false;
        foreach ($layerModel->getState()->getFilters() as $item) {
            if ($item->getFilter()->getFilterOptions() && in_array($item->getFilter()->getFilterOptions()->getDisplay(), array('slider', 'range', 'min_max_slider'))) {
                $result = true;
                break;
            }
        }
        return $result;
    }
}