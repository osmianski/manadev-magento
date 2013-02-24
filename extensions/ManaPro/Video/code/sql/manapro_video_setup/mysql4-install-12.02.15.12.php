<?php
/**
 * @category    Mana
 * @package     ManaPro_Video
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

$table = 'manapro_video/video';
$installer->run("
    DROP TABLE IF EXISTS `{$this->getTable($table)}`;
    CREATE TABLE `{$this->getTable($table)}` (
      `id` bigint NOT NULL AUTO_INCREMENT,
	  `product_id` int(10) unsigned NULL,
	  `default_mask0` int unsigned NOT NULL default '0',
	  `edit_session_id` bigint NOT NULL default '0',
	  `edit_status` bigint NOT NULL default '0',
	  `edit_massaction` tinyint NOT NULL default '0',

      `service` varchar(30) NOT NULL default '',
      `service_video_id` varchar(255) NOT NULL default '',
	  `position` int NOT NULL default '0',
	  `is_base` tinyint NOT NULL default '0',
	  `is_excluded` tinyint NOT NULL default '0',

      PRIMARY KEY  (`id`),
      KEY `product_id` (`product_id`),
	  KEY `edit_session_id` (`edit_session_id`),
	  KEY `edit_status` (`edit_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';

	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_mana_db/edit_session` FOREIGN KEY (`edit_session_id`)
	  REFERENCES `{$installer->getTable('mana_db/edit_session')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

    ALTER TABLE `{$this->getTable($table)}`
    	  ADD CONSTRAINT `FK_{$this->getTable($table)}_catalog/product` FOREIGN KEY (`product_id`)
    	  REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$table = 'manapro_video/video_store';
$installer->run("
    DROP TABLE IF EXISTS `{$this->getTable($table)}`;
    CREATE TABLE `{$this->getTable($table)}` (
      `id` bigint NOT NULL AUTO_INCREMENT,
	  `product_id` int(10) unsigned NULL,
	  `global_id` bigint NULL,
	  `store_id` smallint(5) unsigned NOT NULL,
	  `default_mask0` int unsigned NOT NULL default '0',
	  `edit_session_id` bigint NOT NULL default '0',
	  `edit_status` bigint NOT NULL default '0',
	  `edit_massaction` tinyint NOT NULL default '0',

      `service` varchar(30) NOT NULL default '',
      `service_video_id` varchar(255) NOT NULL default '',
	  `position` int NOT NULL default '0',
	  `is_base` tinyint NOT NULL default '0',
	  `is_excluded` tinyint NOT NULL default '0',

      PRIMARY KEY  (`id`),
      KEY `product_id` (`product_id`),
	  KEY `global_id` (`global_id`),
	  KEY `store_id` (`store_id`),
	  KEY `edit_session_id` (`edit_session_id`),
	  KEY `edit_status` (`edit_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';

	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_mana_db/edit_session` FOREIGN KEY (`edit_session_id`)
	  REFERENCES `{$installer->getTable('mana_db/edit_session')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_manapro_video/video` FOREIGN KEY (`global_id`)
	  REFERENCES `{$installer->getTable('manapro_video/video')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

	ALTER TABLE `{$this->getTable($table)}`
	  ADD CONSTRAINT `FK_{$this->getTable($table)}_core/store` FOREIGN KEY (`store_id`)
	  REFERENCES `{$installer->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;

    ALTER TABLE `{$this->getTable($table)}`
    	  ADD CONSTRAINT `FK_{$this->getTable($table)}_catalog/product` FOREIGN KEY (`product_id`)
    	  REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

");


if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();

