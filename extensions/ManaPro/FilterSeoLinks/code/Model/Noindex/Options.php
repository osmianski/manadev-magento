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
class ManaPro_FilterSeoLinks_Model_Noindex_Options {
    public function detect($layerModel) {
        $filters = array();
        $result = false;
        foreach ($layerModel->getState()->getFilters() as $item) {
            $code = $item->getFilter()->getRequestVar();
            if (!isset($filters[$code])) {
                $filters[$code] = $code;
            }
            else {
                $result = true;
                break;
            }
        }
        return $result;
    }
}