<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $colors ManaPro_FilterColors_Helper_Data */
$colors = Mage::helper(strtolower('ManaPro_FilterColors'));

foreach (Mage::getModel('core/store')->getCollection() as $store) {
	$colors->generateCss($store->getId());
}
