<?php
/**
 * @author Mana Team
 */

/* @var $utils Mana_Core_Helper_Utils */
$utils = Mage::helper('mana_core/utils');

$utils
    ->reindexAll()
    ->disableModuleOutput('Mage_AdminNotification')
    ->disableModuleOutput('Local_Demo')
    ->clearDiskCache();
