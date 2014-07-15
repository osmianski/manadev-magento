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
class Mana_Seo_Helper_PageType_Search extends Mana_Seo_Helper_PageType  {
    public function getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_SEARCH_SUFFIX;
    }

    protected $_urlKey;

    /**
     * @param Mana_Seo_Rewrite_Url $urlModel
     * @return string | bool
     */
    public function getUrlKey($urlModel) {
        if (!$this->_urlKey) {
            /* @var $seo Mana_Seo_Helper_Data */
            $seo = Mage::helper('mana_seo');

            /* @var $logger Mana_Core_Helper_Logger */
            $logger = Mage::helper('mana_core/logger');

            $urlCollection = $seo->getUrlCollection($urlModel->getSchema(), Mana_Seo_Resource_Url_Collection::TYPE_PAGE);
            $urlCollection->addFieldToFilter('url_key',
                Mage::getStoreConfig('mana/seo/search_url_key', $urlModel->getStore()->getId()));
            if (!($result = $urlModel->getUrlKey($urlCollection))) {
                $logger->logSeoUrl(sprintf('WARNING: %s not found', 'Search page URL key'));
            }

            $this->_urlKey = $result;
        }

        return $this->_urlKey['final_url_key'];
    }
}