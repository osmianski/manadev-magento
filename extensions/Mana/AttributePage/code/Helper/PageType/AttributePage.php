<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Helper_PageType_AttributePage extends Mana_Core_Helper_PageType  {
    public function getCurrentSuffix() {
        return Mage::getStoreConfig('mana_attribute_page/attribute_page/suffix');
    }


    public function getRoutePath() {
        return 'mana/attributePage/view';
    }

    public function getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_ATTRIBUTE_PAGE_SUFFIX;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    public function setPage($token) {
        $token
            ->setRoute($this->getRoutePath())
            ->setIsRedirectToSubcategoryPossible(true)
            ->addParameter('id', $token->getPageUrl()->getData('attribute_page_id'));

        return true;
    }

    /**
     * @param Mana_Seo_Rewrite_Url $urlModel
     * @return string | bool
     */
    public function getUrlKey($urlModel) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        if (($attributePageId = $urlModel->getSeoRouteParam('id')) === false) {
            $logger->logSeoUrl(sprintf('WARNING: while resolving %s, %s route parameter is required', 'attribute page URL key', 'id'));
        }
        $urlCollection = $seo->getUrlCollection($urlModel->getSchema(), Mana_Seo_Resource_Url_Collection::TYPE_PAGE);
        $urlCollection->addFieldToFilter('attribute_page_id', $attributePageId);
        $urlCollection->getSelect()->where('main_table.option_page_id IS NULL');
        if (!($result = $urlModel->getUrlKey($urlCollection))) {
            $logger->logSeoUrl(sprintf('WARNING: %s not found by  %s %s', 'attribute page URL key', 'id', $attributePageId));
        }

        return $result['final_url_key'];
    }
}