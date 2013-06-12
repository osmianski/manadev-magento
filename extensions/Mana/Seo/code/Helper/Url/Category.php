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
class Mana_Seo_Helper_Url_Category extends Mana_Seo_Helper_Url {
    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @param Mana_Seo_Model_Url $urlKey
     * @return bool
     */
    public function registerPage($parsedUrl, $urlKey) {
        $parsedUrl
            ->setPageUrlKey($urlKey->getFinalUrlKey())
            ->setRoute('catalog/category/view')
            ->addParameter('id', $urlKey->getCategoryId());

        return true;
    }

    /**
     * @return string
     */
    protected function _getSuffix() {
        /* @var $helper Mage_Catalog_Helper_Category */
        $helper = Mage::helper('catalog/category');
        $result = $helper->getCategoryUrlSuffix();
        if ($result && strpos($result, '.') !== 0) {
            $result = '.' . $result;
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function _getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX;
    }
}