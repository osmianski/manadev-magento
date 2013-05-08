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
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url $url
     * @return bool
     */
    public function isValidUrl($context, $url) {
        /* @var $categoryHelper Mage_Catalog_Helper_Category */
        $categoryHelper = Mage::helper('catalog/category');

        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $currentSuffix = $categoryHelper->getCategoryUrlSuffix();
        $suffixes = $this->_getApplicableSuffixes($context, $currentSuffix, Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX);
        foreach ($suffixes as $suffix => $redirect) {

        }
        // TODO: check context suffix, update url status, remember to handle obsolete URLs by redirecting them to somewhere (where?)

        return true;
    }
}