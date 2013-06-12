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
class Mana_Seo_Helper_Url_CmsPage extends Mana_Seo_Helper_Url {
    /**
     * @param Mana_Seo_Model_ParsedUrl $parsedUrl
     * @param Mana_Seo_Model_Url $urlKey
     * @return bool
     */
    public function registerPage($parsedUrl, $urlKey) {
        $parsedUrl
            ->setPageUrlKey($urlKey->getFinalUrlKey())
            ->setRoute('cms/page/view')
            ->addParameter('id', $urlKey->getCmsPageId());

        return true;
    }

    /**
     * @return string
     */
    protected function _getSuffix() {
        return Mage::getStoreConfig('mana/seo/cms_page_suffix');
    }

    /**
     * @return string
     */
    protected function _getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_CMS_PAGE_SUFFIX;
    }
}