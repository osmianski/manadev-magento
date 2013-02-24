<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$this->startSetup();
$this->cleanCache();

$this->updateAttribute('catalog_product', 'm_featured_from_date', 'backend_model', 'eav/entity_attribute_backend_datetime');
$this->updateAttribute('catalog_product', 'm_featured_to_date', 'backend_model', 'eav/entity_attribute_backend_datetime');

$this->endSetup();
