<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $installer Mana_Core_Resource_Eav_Setup */
$installer = $this;

$installer->startSetup();
/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'm_db_log';
$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable($table)}`;
	CREATE TABLE `{$this->getTable($table)}` (
	  `id` bigint(20) NOT NULL AUTO_INCREMENT,
	  `script_filename` varchar(128) NOT NULL default '',
	  `undo` TEXT NOT NULL,
	  
	  PRIMARY KEY  (`id`),
	  KEY `script_filename` (`script_filename`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
	
");

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'm_db';
$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable($table)}`;
	CREATE TABLE `{$this->getTable($table)}` (
	  `config` TEXT NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
");

$installer->endSetup();