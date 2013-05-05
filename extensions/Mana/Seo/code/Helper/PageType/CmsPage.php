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
class Mana_Seo_Helper_PageType_CmsPage extends Mana_Seo_Helper_PageType  {
    /**
     * @param Mana_Seo_Model_Context $context
     * @param object[] $activeVariations
     * @param object[] $obsoleteVariations
     * @return Mana_Seo_Helper_VariationSource
     */
    public function getVariations($context, &$activeVariations, &$obsoleteVariations) {
        $candidates = $context->getCandidates();
        $activeVariations = array();
        $obsoleteVariations = array();
        $path = $context->getPath();

        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $cmsPageCollection Mage_Cms_Model_Mysql4_Page_Collection */
        $cmsPageCollection = Mage::getResourceModel('cms/page_collection');
        $cmsPageCollection->getSelect()
            ->join(
                array('cps' => $cmsPageCollection->getResource()->getTable('cms/page_store')),
                'main_table.page_id = `cps`.page_id'
            )
            ->where('main_table.identifier IN (?)', $candidates)
            ->where('cps.store_id IN(?)', array(0, $context->getStoreId()))
            ->order('cps.store_id DESC')
            ->order(new Zend_Db_Expr('CHAR_LENGTH(main_table.identifier) DESC'));

        foreach ($cmsPageCollection as $cmsPage) {
            /* @var $cmsPage Mage_Cms_Model_Page */

            /* @var $page Mana_Seo_Model_Page */
            $page = Mage::getModel('mana_seo/page');
            /** @noinspection PhpUndefinedMethodInspection */
            $url = $cmsPage->getIdentifier();
            $urlWithSlash = $mbstring->endsWith($url, '/') ? $url : $url . '/';
            $page
                ->setUrl($url)
                ->setQuery($mbstring->substr($path, $mbstring->strlen($urlWithSlash)));

            $activeVariations[] = $page;
        }

        /* @var $oldIdentifierCollection Mana_Db_Resource_Entity_Collection */
        $oldIdentifierCollection = $dbHelper->getResourceModel('mana_seo/urlHistory_collection');
        $oldIdentifierCollection->getSelect()
            ->where('page_type = ?', Mana_Seo_Model_UrlHistory::TYPE_CMS_PAGE_IDENTIFIER)
            ->where('store_id IN(?)', array(0, $context->getStoreId()))
            ->where('old_url in (?)', $candidates);
        foreach ($oldIdentifierCollection as $historyRecord) {
            /* @var $historyRecord Mana_Seo_Model_UrlHistory */

            /* @var $page Mana_Seo_Model_Page */
            $page = Mage::getModel('mana_seo/page');
            $page
                ->setUrl($historyRecord->getOldUrl())
                ->setCurrentUrl($historyRecord->getNewUrl())
                ->setQuery($mbstring->substr($path, $mbstring->strlen($page->getUrl() . '/')));

            $obsoleteVariations[] = $page;
        }
    }
}