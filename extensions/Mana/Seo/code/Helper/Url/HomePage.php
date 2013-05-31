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
class Mana_Seo_Helper_Url_HomePage extends Mana_Seo_Helper_Url {
    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @param Mana_Seo_Model_Url $urlKey
     * @return bool
     */
    public function registerPage($parsedUrl, $urlKey) {
        $parsedUrl
            ->setPageUrlKey('')
            ->setRoute('cms/index/index');

        return true;
    }

    /**
     * @return string
     */
    protected function _getSuffix() {
        return Mage::getStoreConfig('mana/seo/home_page_suffix');
    }

    /**
     * @return string
     */
    protected function _getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_HOME_PAGE_SUFFIX;
    }
}