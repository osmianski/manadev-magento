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
class ManaPro_FilterSeoLinks_Model_Noindex_Filters {
    public function detect($layerModel) {
        $filter = null;
        $result = false;
        foreach ($layerModel->getState()->getFilters() as $item) {
            if (!$filter) {
                $filter = $item->getFilter();
            }
            elseif ($item->getFilter() != $filter) {
                $result = true;
                break;
            }
        }
        return $result;
    }
}