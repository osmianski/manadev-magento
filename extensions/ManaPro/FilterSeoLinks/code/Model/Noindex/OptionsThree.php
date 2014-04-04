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
class ManaPro_FilterSeoLinks_Model_Noindex_OptionsThree {
    public function detect($layerModel) {
        $filters = array();
        foreach ($layerModel->getState()->getFilters() as $item) {
            $code = $item->getFilter()->getRequestVar();
            if (!isset($filters[$code])) {
                $filters[$code] = array('code' => $code, 'count' => 0);
            }
            $filters[$code]['count']++;
            if ($filters[$code]['count'] >= 3) {
                return true;
            }
        }
        return false;;
    }
}