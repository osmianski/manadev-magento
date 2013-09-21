<?php
/**
 * @author Mana Team
 */

/* @var $utils Mana_Core_Helper_Utils */
$utils = Mage::helper('mana_core/utils');

$utils
    ->disableModuleOutput('Mage_AdminNotification')
    ->disableModuleOutput('Local_Demo')
    ->setStoreConfig('admin/security/session_cookie_lifetime', 3600*24*365)
    ->setStoreConfig('catalog/frontend/flat_catalog_category', 1)
    ->setStoreConfig('catalog/frontend/flat_catalog_product', 1)
    ->setStoreConfig('dev/log/active', 1)
    ->reindexAll()
    ->clearDiskCache();
