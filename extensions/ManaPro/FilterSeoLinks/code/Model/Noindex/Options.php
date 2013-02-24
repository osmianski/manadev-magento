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
    public function process(&$robots, $layerModel) {
        $filters = array();
        $noindex = false;
        foreach (Mage::getSingleton($layerModel)->getState()->getFilters() as $item) {
            $code = $item->getFilter()->getRequestVar();
            if (!isset($filters[$code])) {
                $filters[$code] = $code;
            }
            else {
                $noindex = true;
                break;
            }
        }
        if ($noindex) {
            $robots = 'NOINDEX, NOFOLLOW';
        }
    }
}