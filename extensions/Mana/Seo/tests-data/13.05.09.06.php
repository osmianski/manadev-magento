<?php
/**
 * @author Mana Team
 */

/* @var $this Mana_Core_Test_Setup */

/* @var $utils Mana_Core_Helper_Utils */
$utils = Mage::helper('mana_core/utils');

/* @var $dbHelper Mana_Db_Helper_Data */
$dbHelper = Mage::helper('mana_db');

/* @var $res Mage_Core_Model_Resource */
$res = Mage::getSingleton('core/resource');

/* @var $db Varien_Db_Adapter_Pdo_Mysql */
$db = $res->getConnection('write');

/* @var $history Mana_Seo_Model_UrlHistory */
/* @var $filter Mana_Filters_Model_Filter2 */
/* @var $urlKeyCollection Mana_Seo_Resource_Url_Collection */
/* @var $urlKey Mana_Seo_Model_Url */
/* @var $categoryCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
/* @var $category Mage_Catalog_Model_Category */
/* @var $foundCategory Mage_Catalog_Model_Category */

$dot = '.';
if (Mage::getStoreConfig('catalog/seo/category_url_suffix') == 'html') {
    $dot = '';
}

switch ($this->getTestVariation()) {
    case 'test':
        $utils->setStoreConfig('catalog/seo/category_url_suffix', $dot.'html');

        $history = $dbHelper->getModel('mana_seo/urlHistory');
        $history
            ->setUrlKey($dot.'htm')
            ->setRedirectTo($dot.'html')
            ->setType(Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX)
            ->save();

        $history = $dbHelper->getModel('mana_seo/urlHistory');
        $history
            ->setUrlKey('')
            ->setRedirectTo($dot.'htm')
            ->setType(Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX)
            ->save();
        break;

    case 'test2':
        $utils->setStoreConfig('catalog/seo/category_url_suffix', '');

        $history = $dbHelper->getModel('mana_seo/urlHistory');
        $history
            ->setUrlKey($dot.'htm')
            ->setRedirectTo('')
            ->setType(Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX)
            ->save();

        $history = $dbHelper->getModel('mana_seo/urlHistory');
        $history
            ->setUrlKey($dot.'html')
            ->setRedirectTo($dot.'htm')
            ->setType(Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX)
            ->save();
        break;
}

// apparel category should be saved with apparel_old URL key and then again with apparel key,
// so that apparel_old URL key would be added as a redirect in catalog URL rewrites.
$categoryCollection = $dbHelper->getResourceModel('catalog/category_collection');
$categoryCollection->addAttributeToFilter('url_key', 'apparel');
foreach ($categoryCollection as $foundCategory) {
    $category = $dbHelper->getModel('catalog/category');

    $category
        ->setStoreId(0)
        ->load($foundCategory->getId())
        ->setData('save_rewrites_history', false)
        ->setDataUsingMethod('url_key', 'apparel-old');
    $category->save();


    $category = $dbHelper->getModel('catalog/category');
    $category
        ->setStoreId(0)
        ->load($foundCategory->getId())
        ->setData('save_rewrites_history', true)
        ->setDataUsingMethod('url_key', 'apparel')
        ->setDataUsingMethod('url_key_create_redirect', 'apparel-old');
    $category->save();
}

// contract_ratio and computer_manufacturers filter names should always be visible
foreach (array('contrast_ratio', 'computer_manufacturers') as $key) {
    $filter = $dbHelper->getModel('mana_filters/filter2');
    $filter->load($key, 'code');
    $dbHelper->updateDefaultableField($filter, 'include_in_url', Mana_Filters_Resource_Filter2::DM_INCLUDE_IN_URL,
        array('include_in_url' => Mana_Seo_Model_Source_IncludeInUrl::ALWAYS), array());
    $filter->save();
}

// AMD value should always be prepended with filter name
$urlKeyCollection = $dbHelper->getResourceModel('mana_seo/url_collection');
$urlKeyCollection->addFieldToFilter('url_key', 'amd');
foreach ($urlKeyCollection as $urlKey) {
    $urlKey
        ->setForceIncludeFilterName(1)
        ->save();
}

// black, blue and dress values should have old URL keys. The same with color attribute
foreach (array('black', 'blue', 'dress', 'color') as $key) {
    $urlKeyCollection = $dbHelper->getResourceModel('mana_seo/url_collection');
    $urlKeyCollection->addFieldToFilter('url_key', $key);
    foreach ($urlKeyCollection as $urlKey) {
        $urlKey
            ->setUrlKey($urlKey->getUrlKey().'-old')
            ->setUniqueKey($urlKey->getUniqueKey() . '-old')
            ->save();
    }
}

$utils->reindex('mana_seo');
$utils->reindex('mana_seo_url');
