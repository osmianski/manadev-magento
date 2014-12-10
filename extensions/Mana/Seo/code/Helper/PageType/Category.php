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
    public function getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    public function setPage($token) {
        parent::setPage($token);
        $token
            ->setIsRedirectToSubcategoryPossible(true)
            ->addParameter('id', $token->getPageUrl()->getCategoryId());
        return true;
    }

    protected $_urlKeys = array();
    /**
     * @param Mana_Seo_Rewrite_Url $urlModel
     * @return string | bool
     */
    public function getUrlKey($urlModel) {
        Mana_Core_Profiler2::start(__METHOD__);
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        if (($categoryId = $urlModel->getSeoRouteParam('id')) === false) {
            $logger->logSeoUrl(sprintf('WARNING: while resolving %s, %s route parameter is required', 'category URL key', 'id'));
        }
        if (!isset($this->_urlKeys[$categoryId])) {
            $urlCollection = $seo->getUrlCollection($urlModel->getSchema(), Mana_Seo_Resource_Url_Collection::TYPE_PAGE);
            $urlCollection->addFieldToFilter('category_id', $categoryId);
            if (!($result = $urlModel->getUrlKey($urlCollection))) {
                $logger->logSeoUrl(sprintf('WARNING: %s not found by  %s %s', 'category URL key', 'id', $categoryId));
            }
            $this->_urlKeys[$categoryId] = $result;
        }

        Mana_Core_Profiler2::stop();

        return $this->_urlKeys[$categoryId]['final_url_key'];
    }
}