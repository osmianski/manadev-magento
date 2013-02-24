<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'm_attribute';
$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable($table)}`;
	CREATE TABLE `{$this->getTable($table)}` (
	  `attribute_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
	  `is_key` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `is_global` tinyint(1) unsigned NOT NULL DEFAULT '1',
	  `has_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `default_model` varchar(255) NOT NULL DEFAULT '',
	  `default_source` varchar(255) NOT NULL DEFAULT '',
	  `default_mask_field` varchar(255) NOT NULL DEFAULT '',
	  `default_mask` int(11) NOT NULL DEFAULT '0',
	  
	  PRIMARY KEY (`attribute_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
	
");

$installer->endSetup();