<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Mana_AttributePage_Helper_PageType_AttributePage extends Mana_Seo_Helper_PageType {
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

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        $currentSuffix = $attributePageHelper->getAttributePageSuffix();
        $suffixes = $this->_getApplicableSuffixes($context, $currentSuffix, Mana_Seo_Model_UrlHistory::TYPE_ATTRIBUTE_PAGE_SUFFIX);

        foreach ($suffixes as $suffix => $allObsolete) {
            /* @var $attributePageCollection Mana_Db_Resource_Entity_Collection */
            $attributePageCollection = $dbHelper->getResourceModel('mana_attributepage/page/store_flat_collection');

            $candidates = array();
            $pageUrlCandidate = $this->_removeSuffix($context->getPath(), $suffix);
            $candidates[] = $pageUrlCandidate . $context->getOriginalSlash();
            $candidates[] = $pageUrlCandidate . $context->getAlternativeSlash();

            $attributePageCollection->getSelect()
                ->where('url_key IN (?)', $candidates)
                ->where('store_id IN(?)', array(0, $context->getStoreId()))
                ->order('store_id DESC')
                ->order(new Zend_Db_Expr('CHAR_LENGTH(url_key) DESC'));

            foreach ($attributePageCollection as $attributePage) {
                /* @var $attributePage Mana_AttributePage_Model_Page */

                /* @var $page Mana_Seo_Model_Page */
                $page = Mage::getModel('mana_seo/page');
                /** @noinspection PhpUndefinedMethodInspection */
                $url = $attributePage->getUrlKey();

                /** @noinspection PhpUndefinedMethodInspection */
                $currentUrl = $attributePage->getUrlKey();
                $page
                    ->setUrl($url)
                    ->setCurrentUrl($currentUrl)
                    ->setSuffix($suffix)
                    ->setCurrentSuffix($currentSuffix)
                    ->setQuery('');

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
                ->where('page_type = ?', Mana_Seo_Model_UrlHistory::TYPE_ATTRIBUTE_PAGE_URL_KEY)
                ->where('store_id IN(?)', array(0, $context->getStoreId()))
                ->where('old_url IN (?)', $candidates);
            foreach ($oldUrlKeyCollection as $historyRecord) {
                /* @var $page Mana_Seo_Model_Page */
                $page = Mage::getModel('mana_seo/page');
                /** @noinspection PhpUndefinedMethodInspection */
                $url = $historyRecord->getOldUrl();

                /** @noinspection PhpUndefinedMethodInspection */
                $currentUrl = $historyRecord->getNewUrl();

                $page
                    ->setUrl($url)
                    ->setCurrentUrl($currentUrl)
                    ->setSuffix($suffix)
                    ->setCurrentSuffix($currentSuffix)
                    ->setQuery('');

                $obsoleteVariations[] = $page;
            }
        }
    }
}