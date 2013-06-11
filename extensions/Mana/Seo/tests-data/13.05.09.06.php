<?php
/**
 * @author Mana Team
 */

/* @var $this Mana_Core_Test_Setup */

/* @var $utils Mana_Core_Helper_Utils */
$utils = Mage::helper('mana_core/utils');

/* @var $db Mana_Db_Helper_Data */
$db = Mage::helper('mana_db');

/* @var $history Mana_Seo_Model_UrlHistory */
/* @var $filter Mana_Filters_Model_Filter2 */
/* @var $urlKeyCollection Mana_Seo_Resource_Url_Collection */
/* @var $urlKey Mana_Seo_Model_Url */
/* @var $categoryCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
/* @var $category Mage_Catalog_Model_Category */
/* @var $foundCategory Mage_Catalog_Model_Category */

switch ($this->getTestVariation()) {
    case 'test':
        $utils->setStoreConfig('catalog/seo/category_url_suffix', '.html');

        $history = $db->getModel('mana_seo/urlHistory');
        $history
            ->setUrlKey('.htm')
            ->setRedirectTo('.html')
            ->setType(Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX)
            ->save();

        $history = $db->getModel('mana_seo/urlHistory');
        $history
            ->setUrlKey('')
            ->setRedirectTo('.htm')
            ->setType(Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX)
            ->save();
        break;

    case 'test2':
        $utils->setStoreConfig('catalog/seo/category_url_suffix', '');

        $history = $db->getModel('mana_seo/urlHistory');
        $history
            ->setUrlKey('.htm')
            ->setRedirectTo('')
            ->setType(Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX)
            ->save();

        $history = $db->getModel('mana_seo/urlHistory');
        $history
            ->setUrlKey('.html')
            ->setRedirectTo('.htm')
            ->setType(Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX)
            ->save();
        break;
}

// contract_ratio filter name should always be visible
$filter = $db->getModel('mana_filters/filter2');
$filter->load('contrast_ratio', 'code');
$db->updateDefaultableField($filter, 'include_in_url', Mana_Filters_Resource_Filter2::DM_INCLUDE_IN_URL,
    array('include_in_url' => Mana_Seo_Model_Source_IncludeInUrl::ALWAYS), array());
$filter->save();

// AMD value should always be prepended with filter name
$urlKeyCollection = $db->getResourceModel('mana_seo/url_collection');
$urlKeyCollection->addFieldToFilter('url_key', 'amd');
foreach ($urlKeyCollection as $urlKey) {
    $urlKey->setForceIncludeFilterName(1);
    $urlKey->save();
}

// apparel category should be saved with apparel_old URL key and then again with apparel key,
// so that apparel_old URL key would be added as a redirect in catalog URL rewrites.
$categoryCollection = $db->getResourceModel('catalog/category_collection');
$categoryCollection->addAttributeToFilter('url_key', 'apparel');
foreach ($categoryCollection as $foundCategory) {
    $category = $db->getModel('catalog/category');

    $category
        ->setStoreId(0)
        ->load($foundCategory->getId())
        ->setData('save_rewrites_history', false)
        ->setDataUsingMethod('url_key', 'apparel-old');
    $category->save();

    $category
        ->setStoreId(0)
        ->load($foundCategory->getId())
        ->setData('save_rewrites_history', true)
        ->setDataUsingMethod('url_key', 'apparel')
        ->setDataUsingMethod('url_key_create_redirect', 'apparel-old');
    $category->save();
}

$utils->reindex('mana_seo');
$utils->reindex('mana_seo_url');
