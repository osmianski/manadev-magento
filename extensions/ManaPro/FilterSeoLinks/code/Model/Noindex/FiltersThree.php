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
class ManaPro_FilterSeoLinks_Model_Noindex_FiltersThree {
    public function detect($layerModel) {
        $filters = array();
        foreach ($layerModel->getState()->getFilters() as $item) {
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
            if (count($filters) >= 3) {
                return true;
            }
        }
        return false;
    }
}