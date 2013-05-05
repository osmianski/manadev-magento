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
    /**
     * @param Mana_Seo_Model_Context $context
     * @param object[] $activeVariations
     * @param object[] $obsoleteVariations
     * @return Mana_Seo_Interface_VariationSource
     */
    public function getVariations($context, &$activeVariations, &$obsoleteVariations) {
        $activeVariations = array();
        $obsoleteVariations = array();

        /* @var $categoryHelper Mage_Catalog_Helper_Category */
        $categoryHelper = Mage::helper('catalog/category');

        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $currentSuffix = $categoryHelper->getCategoryUrlSuffix();
        $suffixes = $this->_getApplicableSuffixes($context, $currentSuffix, Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX);

        foreach ($suffixes as $suffix => $allObsolete) {
            /* @var $rewriteCollection Mage_Core_Model_Mysql4_Url_Rewrite_Collection */
            $rewriteCollection = Mage::getResourceModel('core/url_rewrite_collection');

            $lastIndex = count($context->getCandidates()) - 1;
            $candidates = array();
            foreach ($context->getCandidates() as $index => $pageUrlCandidate) {
                if ($index == $lastIndex) {
                    $pageUrlCandidate = $this->_removeSuffix($pageUrlCandidate, $suffix);
                }
                $pageUrlCandidate = $this->_addSuffix($pageUrlCandidate, $currentSuffix);
                $candidates[] = $pageUrlCandidate . $context->getOriginalSlash();
                $candidates[] = $pageUrlCandidate . $context->getAlternativeSlash();
            }

            $rewriteCollection->getSelect()
                ->where('request_path IN (?)', $candidates)
                ->where('store_id IN(?)', array(0, $context->getStoreId()))
                ->where('category_id IS NOT NULL')
                ->order('store_id DESC')
                ->order(new Zend_Db_Expr('CHAR_LENGTH(request_path) DESC'));

            foreach ($rewriteCollection as $rewrite) {
                /* @var $rewrite Mage_Core_Model_Url_Rewrite */

                /* @var $page Mana_Seo_Model_Page */
                $page = Mage::getModel('mana_seo/page');
                /** @noinspection PhpUndefinedMethodInspection */
                $url = $this->_removeSuffix(trim($rewrite->getRequestPath(), '/'), $currentSuffix);
                $urlWithSlash = $mbstring->endsWith($url, '/') ? $url : $url . '/';
                /** @noinspection PhpUndefinedMethodInspection */
                $currentUrl = $this->_removeSuffix(trim($rewrite->getTargetPath(), '/'), $currentSuffix);
                $path = $this->_removeSuffix($context->getPath(), $suffix);
                $page
                    ->setUrl($url)
                    ->setCurrentUrl($currentUrl)
                    ->setSuffix($suffix)
                    ->setCurrentSuffix($currentSuffix)
                    ->setQuery($mbstring->substr($path, $mbstring->strlen($urlWithSlash)));

                /** @noinspection PhpUndefinedMethodInspection */
                if ($allObsolete || $rewrite->getOptions() == 'RP') {
                    $obsoleteVariations[] = $page;
                }
                else {
                    $activeVariations[] = $page;
                }
            }
        }
    }
}