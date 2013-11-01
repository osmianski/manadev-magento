<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'mana_filters/filter2';
$installer->run("
	ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (
		`color_state_display` varchar(20) NOT NULL default ''
	);
");

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'mana_filters/filter2_store';
$installer->run("
	ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (
		`color_state_display` varchar(20) NOT NULL default ''
	);
");


$installer->endSetup();

if (!Mage::registry('m_run_db_replication')) {
	Mage::register('m_run_db_replication', true);
}
