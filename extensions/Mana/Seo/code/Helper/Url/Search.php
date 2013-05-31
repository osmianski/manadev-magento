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
class Mana_Seo_Helper_Url_Search extends Mana_Seo_Helper_Url {
    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @param Mana_Seo_Model_Url $urlKey
     * @return bool
     */
    public function registerPage($parsedUrl, $urlKey) {
        if ($parsedUrl->hasParameter('q')) {
            $parsedUrl
                ->setPageUrlKey('catalogsearch/result')
                ->setRoute('catalogsearch/result/index');

            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @return string
     */
    protected function _getSuffix() {
        return Mage::getStoreConfig('mana/seo/search_suffix');
    }

    /**
     * @return string
     */
    protected function _getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_SEARCH_SUFFIX;
    }
}