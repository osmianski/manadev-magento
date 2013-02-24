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
    public function process(&$robots, $layerModel) {
        $filter = null;
        $noindex = false;
        foreach (Mage::getSingleton($layerModel)->getState()->getFilters() as $item) {
            if ($item->getFilter()->getFilterOptions() && $item->getFilter()->getFilterOptions()->getDisplay() == 'slider') {
                $noindex = true;
                break;
            }
        }
        if ($noindex) {
            $robots = 'NOINDEX, NOFOLLOW';
        }
    }
}