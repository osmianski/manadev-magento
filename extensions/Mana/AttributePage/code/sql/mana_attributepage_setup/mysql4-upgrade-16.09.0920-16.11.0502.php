<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

if (defined('COMPILER_INCLUDE_PATH')) {
    throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
if (method_exists($this->getConnection(), 'allowDdlCache')) {
    $this->getConnection()->allowDdlCache();
}

$tables = array(
    $this->getTable('mana_attributepage/attributePage_globalCustomSettings'),
    $this->getTable('mana_attributepage/attributePage_storeCustomSettings'),
    $this->getTable('mana_attributepage/attributePage_store'),
);

foreach ($tables as $table) {
    $installer->run("ALTER TABLE `$table` ADD `description_bottom` mediumtext NOT NULL");
}

$tables = array(
    $this->getTable('mana_attributepage/optionPage_globalCustomSettings'),
    $this->getTable('mana_attributepage/optionPage_global'),
    $this->getTable('mana_attributepage/optionPage_storeCustomSettings'),
    $this->getTable('mana_attributepage/optionPage_store'),
);

foreach ($tables as $table) {
    $installer->run("ALTER TABLE `$table` ADD `description_bottom` mediumtext NOT NULL");
}

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();

Mage::helper('mana_core/db')->scheduleReindexing('mana_attribute_page');
Mage::helper('mana_core/db')->scheduleReindexing('mana_option_page');
