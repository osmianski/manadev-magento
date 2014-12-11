<?php

/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$script = "
ALTER TABLE %s
  ADD COLUMN tags VARCHAR(255) NULL;
";

$tables = array(
    $installer->getTable('mana_content/page_globalCustomSettings'),
    $installer->getTable('mana_content/page_store'),
    $installer->getTable('mana_content/page_storeCustomSettings')
);

foreach($tables as $table) {
    $installer->run(sprintf($script,$table));
}
$installer->endSetup();