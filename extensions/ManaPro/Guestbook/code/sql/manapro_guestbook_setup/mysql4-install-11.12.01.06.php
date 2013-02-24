<?php
/** 
 * @category    Mana
 * @package     ManaPro_Guestbook
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}
/* @var $installer Mage_Core_Model_Resource_Setup */$installer = $this;
$installer->startSetup();

$table = 'manapro_guestbook/post';
$installer->run("
  DROP TABLE IF EXISTS `{$this->getTable($table)}`;
  CREATE TABLE `{$this->getTable($table)}` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `store_id` smallint(5) unsigned NOT NULL,
    `status` tinyint NOT NULL default '0',
    `email` varchar(255) NOT NULL default '',
    `name` varchar(80) NOT NULL default '',
    `text` text NOT NULL,
    `country_id` varchar(2) NOT NULL default '',
    `region` varchar(128) NOT NULL default '',
    `region_id` mediumint(8) unsigned NULL,

    PRIMARY KEY  (`id`),
	KEY `store_id` (`store_id`),
	KEY `status` (`status`),
	KEY `country_id` (`country_id`),
	KEY `region_id` (`region_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';

  ALTER TABLE `{$this->getTable($table)}`
  	  ADD CONSTRAINT `FK_{$this->getTable($table)}_core/store` FOREIGN KEY (`store_id`)
  	  REFERENCES `{$installer->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$installer->endSetup();