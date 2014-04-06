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
class Mana_AttributePage_Helper_PageType_OptionPage extends Mana_Core_Helper_PageType  {
    public function getCurrentSuffix() {
        return Mage::getStoreConfig('mana_attribute_page/option_page/suffix');
    }


    public function getRoutePath() {
        return 'mana/optionPage/view';
    }

    public function getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_OPTION_PAGE_SUFFIX;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    public function setPage($token) {
        $token
            ->setRoute($this->getRoutePath())
            ->setIsRedirectToSubcategoryPossible(false)
            ->addParameter('id', $token->getPageUrl()->getData('option_page_id'));

        return true;
    }

    protected $_urlKeys = array();

    /**
     * @param Mana_Seo_Rewrite_Url $urlModel
     * @return string | bool
     */
    public function getUrlKey($urlModel) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        if (($optionPageId = $urlModel->getSeoRouteParam('id')) === false) {
            $logger->logSeoUrl(sprintf('WARNING: while resolving %s, %s route parameter is required', 'option page URL key', 'id'));
        }
        if (!isset($this->_urlKeys[$optionPageId])) {
            $urlCollection = $seo->getUrlCollection($urlModel->getSchema(), Mana_Seo_Resource_Url_Collection::TYPE_PAGE);
            $urlCollection->addFieldToFilter('option_page_id', $optionPageId);
            if (!($result = $urlModel->getUrlKey($urlCollection))) {
                $logger->logSeoUrl(sprintf('WARNING: %s not found by  %s %s', 'option page URL key', 'id', $optionPageId));
            }

            $this->_urlKeys[$optionPageId] = $result;
        }

        return $this->_urlKeys[$optionPageId]['final_url_key'];
    }

    /**
     * @return bool|string
     */
    public function getConditionLabel() {
        return $this->__('Attribute Option Page');
    }
}
