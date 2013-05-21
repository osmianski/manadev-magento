<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Helper_VariationPoint_Suffix_Category extends Mana_Seo_Helper_VariationPoint_Suffix {
    protected $_historyType = Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX;

    public function getCurrentSuffix() {
        /* @var $categoryHelper Mage_Catalog_Helper_Category */
        $categoryHelper = Mage::helper('catalog/category');
        return $categoryHelper->getCategoryUrlSuffix();
    }
}