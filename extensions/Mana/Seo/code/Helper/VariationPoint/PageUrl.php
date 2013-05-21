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
class Mana_Seo_Helper_VariationPoint_PageUrl extends Mana_Seo_Helper_VariationPoint {
    /**
     * @param Mana_Seo_Model_Context $context
     * @return Mana_Seo_Helper_VariationPoint_PageUrl
     */
    protected function _before($context) {
        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $candidates = $this->_parsePath($context->getPath(), $context->getSchema()->getQuerySeparator(), false);
        $lastCandidate = $candidates[count($candidates) - 1];
        if (($pos = $mbstring->strpos($lastCandidate, '.')) !== false) {
            $candidates[] = $mbstring->substr($lastCandidate, 0, $pos);
        }
        $context->pushData('candidates', $candidates);

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url $pageUrl
     * @return bool
     */
    protected function _register($context, $pageUrl) {
        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        $logger->beginSeo("Checking page '{$pageUrl->getUrlKey()}' (type: {$pageUrl->getType()}) ...");
        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $context->setPageUrl($pageUrl);

        $path = $context->getPath();
        $path = $mbstring->substr($path, $mbstring->strlen($pageUrl->getUrlKey()));
        if ($mbstring->startsWith($path, $context->getSchema()->getQuerySeparator())) {
            $path = $mbstring->substr($path, $mbstring->strlen($context->getSchema()->getQuerySeparator()));
            $context->setLastSeparator($context->getSchema()->getQuerySeparator());
        }
        $context->pushData('path', $path);

        return true;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url $pageUrl
     * @return Mana_Seo_Helper_VariationPoint_PageUrl
     */
    protected function _unregister(/** @noinspection PhpUnusedParameterInspection */$context, $pageUrl) {
        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        $context->unsetData('page_url');
        $context->popData('path');

        $logger->endSeo();
        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @return Mana_Seo_Helper_VariationPoint_PageUrl
     */
    protected function _after($context) {
        $context->popData('candidates');

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url[] $activePageUrls
     * @param Mana_Seo_Model_Url[] $obsoletePageUrls
     * @return Mana_Seo_Helper_VariationPoint_PageUrl
     */
    protected function _getPageUrls($context, &$activePageUrls, &$obsoletePageUrls) {
        $activePageUrls = array();
        $obsoletePageUrls = array();

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/url_collection');
        $collection
            ->setStoreFilter($context->getStoreId())
            ->addFieldToFilter('url_key', array('in' => $context->getCandidates()))
            ->addFieldToFilter('is_page', 1)
            ->addFieldToFilter('status', array(
                'in' => array(
                    Mana_Seo_Model_Url::STATUS_ACTIVE,
                    Mana_Seo_Model_Url::STATUS_OBSOLETE
                )
            ));

        foreach ($collection as $pageUrl) {
            /* @var $pageUrl Mana_Seo_Model_Url */
            if ($pageUrl->getStatus() == Mana_Seo_Model_Url::STATUS_ACTIVE) {
                $activePageUrls[] = $pageUrl;
            }
            else {
                $obsoletePageUrls[] = $pageUrl;
            }
        }
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @return bool
     */
    public function match($context) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        $allObsoletePageUrls = array();
        $action = $context->getAction();

        $this->_before($context);
        $this->_getPageUrls($context, $activePageUrls, $obsoletePageUrls);
        foreach ($activePageUrls as $pageUrl) {
            /* @var $pageUrl Mana_Seo_Model_Url */
            if ($this->_matchDeeper($context, $pageUrl, $seo)) {
                return true;
            }
        }
        $allObsoletePageUrls = array_merge($allObsoletePageUrls, $obsoletePageUrls);

        $context->setAction(Mana_Seo_Model_Context::ACTION_REDIRECT);
        foreach ($allObsoletePageUrls as $pageUrl) {
            if ($this->_matchDeeper($context, $pageUrl, $seo)) {
                return true;
            }
        }

        $context->setAction($action);
        $this->_after($context);

        return false;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url $pageUrl
     * @param Mana_Seo_Helper_Data $seo
     * @return bool
     */
    protected function _matchDeeper($context, $pageUrl, $seo) {
        if ($this->_register($context, $pageUrl)) {
            if ($suffixVariationPoint = $pageUrl->getHelper()->getSuffixVariationPoint($context)) {
                if ($suffixVariationPoint->match($context)) {
                    return true;
                }
            }
            else {
                if ($seo->getParameterVariationPoint()->match($context)) {
                    return true;
                }
            }

            $this->_unregister($context, $pageUrl);
        }
        return false;
    }

}