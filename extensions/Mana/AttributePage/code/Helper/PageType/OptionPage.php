<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Mana_AttributePage_Helper_PageType_OptionPage extends Mana_Seo_Helper_PageType {
    /**
     * @param Mana_Seo_Model_Context $context
     * @param object[] $activeVariations
     * @param object[] $obsoleteVariations
     * @return Mana_Seo_Helper_VariationSource
     */
    public function getVariations($context, &$activeVariations, &$obsoleteVariations) {
        $activeVariations = array();
        $obsoleteVariations = array();

        /* @var $attributePageHelper Mana_AttributePage_Helper_Data */
        $attributePageHelper = Mage::helper('mana_attributepage');

        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        $currentSuffix = $attributePageHelper->getOptionPageSuffix();
        $suffixes = $this->_getApplicableSuffixes($context, $currentSuffix, Mana_Seo_Model_UrlHistory::TYPE_OPTION_PAGE_SUFFIX);

        foreach ($suffixes as $suffix => $allObsolete) {
            /* @var $optionPageCollection Mana_Db_Resource_Entity_Collection */
            $optionPageCollection = $dbHelper->getResourceModel('mana_attributepage/option_page/store_flat_collection');

            $lastIndex = count($context->getCandidates()) - 1;
            $candidates = array();
            foreach ($context->getCandidates() as $index => $pageUrlCandidate) {
                if ($index == $lastIndex) {
                    $pageUrlCandidate = $this->_removeSuffix($pageUrlCandidate, $suffix);
                }
                $candidates[] = $pageUrlCandidate . $context->getOriginalSlash();
                $candidates[] = $pageUrlCandidate . $context->getAlternativeSlash();
            }

            $optionPageCollection->getSelect()
                ->where('url_key IN (?)', $candidates)
                ->where('store_id IN(?)', array(0, $context->getStoreId()))
                ->order('store_id DESC')
                ->order(new Zend_Db_Expr('CHAR_LENGTH(url_key) DESC'));

            foreach ($optionPageCollection as $optionPage) {
                /* @var $optionPage Mana_Db_Model_Entity */

                /* @var $page Mana_Seo_Model_Page */
                $page = Mage::getModel('mana_seo/page');
                /** @noinspection PhpUndefinedMethodInspection */
                $url = $optionPage->getUrlKey();
                $urlWithSlash = $mbstring->endsWith($url, '/') ? $url : $url . '/';

                /** @noinspection PhpUndefinedMethodInspection */
                $currentUrl = $optionPage->getUrlKey();
                $path = $this->_removeSuffix($context->getPath(), $suffix);
                $page
                    ->setUrl($url)
                    ->setCurrentUrl($currentUrl)
                    ->setSuffix($suffix)
                    ->setCurrentSuffix($currentSuffix)
                    ->setQuery($mbstring->substr($path, $mbstring->strlen($urlWithSlash)));

                if ($allObsolete) {
                    $obsoleteVariations[] = $page;
                }
                else {
                    $activeVariations[] = $page;
                }
            }

            /* @var $oldUrlKeyCollection Mana_Db_Resource_Entity_Collection */
            $oldUrlKeyCollection = $dbHelper->getResourceModel('mana_seo/urlHistory_collection');
            $oldUrlKeyCollection->getSelect()
                ->where('page_type = ?', Mana_Seo_Model_UrlHistory::TYPE_OPTION_PAGE_URL_KEY)
                ->where('store_id IN(?)', array(0, $context->getStoreId()))
                ->where('old_url IN (?)', $candidates);
            foreach ($oldUrlKeyCollection as $historyRecord) {
                /* @var $page Mana_Seo_Model_Page */
                $page = Mage::getModel('mana_seo/page');
                /** @noinspection PhpUndefinedMethodInspection */
                $url = $historyRecord->getOldUrl();
                $urlWithSlash = $mbstring->endsWith($url, '/') ? $url : $url . '/';

                /** @noinspection PhpUndefinedMethodInspection */
                $currentUrl = $historyRecord->getNewUrl();

                $path = $this->_removeSuffix($context->getPath(), $suffix);
                $page
                    ->setUrl($url)
                    ->setCurrentUrl($currentUrl)
                    ->setSuffix($suffix)
                    ->setCurrentSuffix($currentSuffix)
                    ->setQuery($mbstring->substr($path, $mbstring->strlen($urlWithSlash)));

                $obsoleteVariations[] = $page;
            }
        }
    }
}