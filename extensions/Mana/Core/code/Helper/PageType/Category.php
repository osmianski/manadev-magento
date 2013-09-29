<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_PageType_Category extends Mana_Core_Helper_PageType  {
    public function getCurrentSuffix() {
        /* @var $categoryHelper Mage_Catalog_Helper_Category */
        $categoryHelper = Mage::helper('catalog/category');

        return $categoryHelper->getCategoryUrlSuffix();
    }


    public function getRoutePath() {
        return 'catalog/category/view';
    }
}