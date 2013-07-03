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
class ManaPro_FilterSeoLinks_Model_Noindex_Any {
    public function process(&$robots, $layerModel) {
        $filter = null;
        $noindex = false;
        foreach (Mage::getSingleton($layerModel)->getState()->getFilters() as $item) {
            $noindex = true;
            break;
        }
        if ($noindex) {
            $robots = Mage::getStoreConfigFlag('mana_filters/seo/no_follow') ? 'NOINDEX, NOFOLLOW' : 'NOINDEX, FOLLOW';
        }
    }
}