<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

Mage::register('m_prevent_indexing_on_save', true, true);

/* @var $setup Mana_Db_Model_Setup */
$setup = Mage::getModel('mana_db/setup');
$setup->run($this, 'mana_seo', '13.04.18.07');

/* @var $db Mana_Db_Helper_Data */
$db = Mage::helper('mana_db');

/* @var $utils Mana_Core_Helper_Utils */
$utils = Mage::helper('mana_core/utils');

/* @var $dbHelper Mana_Core_Helper_Db */
$dbHelper = Mage::helper('mana_core/db');

/* @var $schema Mana_Seo_Model_Schema */
$schema = $db->getModel('mana_seo/schema');
$schema
    ->overrideName('MANAdev 2013')
    ->overrideSymbols(json_encode($dbHelper->getSeoSymbols()))
    ->overrideStatus(Mana_Seo_Model_Schema::STATUS_ACTIVE)
    ->overrideRedirectParameterOrder(1)
    ->overrideQuerySeparator('/')
    ->overrideParamSeparator('/')
    ->overrideFirstValueSeparator('/')
    ->overrideMultipleValueSeparator('-')
    ->overridePriceSeparator('-')
    ->overrideUseRangeBounds(1)
    ->overrideUseFilterLabels(1)
    ->overrideToolbarUrlKeys(json_encode(array(
        array('internal_name' => 'p', 'name' => 'page', 'position' => 9900),
        array('internal_name' => 'order', 'name' => 'sort-by', 'position' => 9910),
        array('internal_name' => 'dir', 'name' => 'sort-direction', 'position' => 9920),
        array('internal_name' => 'mode', 'name' => 'mode', 'position' => 9930),
        array('internal_name' => 'limit', 'name' => 'show', 'position' => 9940),
    )))
    ->overrideIncludeFilterName(1)
    ->overrideRedirectToSubcategory(1)
    ->overrideCanonicalCategory(1)
    ->overrideCanonicalSearch(1)
    ->overrideCanonicalCms(1)
    ->overrideCanonicalFilters(1)
    ->overrideCanonicalLimitAll(1)
    ->overridePrevNextProductList(1)
    ->save();

$schema = $db->getModel('mana_seo/schema');
$schema
    ->overrideName('MANAdev 2011')
    ->overrideSymbols(json_encode(array(
        array('symbol' => '-', 'substitute' => $utils->getStoreConfig('mana_filters/seo/dash')),
        array('symbol' => '/', 'substitute' => $utils->getStoreConfig('mana_filters/seo/slash')),
        array('symbol' => '+', 'substitute' => $utils->getStoreConfig('mana_filters/seo/plus')),
        array('symbol' => '_', 'substitute' => $utils->getStoreConfig('mana_filters/seo/underscore')),
        array('symbol' => '\'', 'substitute' => $utils->getStoreConfig('mana_filters/seo/quote')),
        array('symbol' => '"', 'substitute' => $utils->getStoreConfig('mana_filters/seo/double_quote')),
        array('symbol' => '%', 'substitute' => $utils->getStoreConfig('mana_filters/seo/percent')),
        array('symbol' => '#', 'substitute' => $utils->getStoreConfig('mana_filters/seo/hash')),
        array('symbol' => '&', 'substitute' => $utils->getStoreConfig('mana_filters/seo/ampersand')),
        array('symbol' => ' ', 'substitute' => $utils->getStoreConfig('mana_filters/seo/space')),
    )))
    ->overrideStatus(Mana_Seo_Model_Schema::STATUS_OBSOLETE)
    ->overrideRedirectParameterOrder(0)
    ->overrideQuerySeparator('/' . $utils->getStoreConfig('mana_filters/seo/conditional_word') . '/')
    ->overrideParamSeparator('/')
    ->overrideFirstValueSeparator('/')
    ->overrideMultipleValueSeparator('_')
    ->overridePriceSeparator(',')
    ->overrideUseRangeBounds(0)
    ->overrideUseFilterLabels($utils->getStoreConfig('mana_filters/seo/use_label'))
    ->overrideToolbarUrlKeys(json_encode(array(
        array('internal_name' => 'p', 'name' => 'p', 'position' => 9900),
        array('internal_name' => 'order', 'name' => 'order', 'position' => 9910),
        array('internal_name' => 'dir', 'name' => 'dir', 'position' => 9920),
        array('internal_name' => 'mode', 'name' => 'mode', 'position' => 9930),
        array('internal_name' => 'limit', 'name' => 'limit', 'position' => 9940),
    )))
    ->overrideIncludeFilterName(1)
    ->overrideRedirectToSubcategory(0)
    ->overrideCanonicalCategory(0)
    ->overrideCanonicalSearch(0)
    ->overrideCanonicalCms(0)
    ->overrideCanonicalFilters(0)
    ->overrideCanonicalLimitAll(0)
    ->overridePrevNextProductList(0)
    ->save();

Mage::unregister('m_prevent_indexing_on_save');
$setup->scheduleReindexing('mana_db');
$setup->scheduleReindexing('mana_seo_url');