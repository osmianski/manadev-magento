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
class Mana_Seo_Helper_PageType_Category extends Mana_Seo_Helper_PageType  {
    public function getCurrentSuffix() {
        /* @var $categoryHelper Mage_Catalog_Helper_Category */
        $categoryHelper = Mage::helper('catalog/category');

        return $categoryHelper->getCategoryUrlSuffix();
    }

    public function getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    public function setPage($token) {
        $token
            ->setRoute('catalog/category/view')
            ->addParameter('id', $token->getPageUrl()->getCategoryId());
        return true;
    }
}