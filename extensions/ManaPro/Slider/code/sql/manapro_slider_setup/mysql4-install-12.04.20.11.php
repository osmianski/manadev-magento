<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
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

$table = 'manapro_slider/item';
$installer->run("
  DROP TABLE IF EXISTS `{$this->getTable($table)}`;
  CREATE TABLE `{$this->getTable($table)}` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `widget_id` int(11) unsigned NULL,
    `type` varchar(10) NOT NULL default 'cms_block',
    `cms_block_id` smallint(6) NULL,
    `product_id` int(10) unsigned NULL,
    `position` int NOT NULL default '500',

    `edit_session_id` bigint NOT NULL default '0',
    `edit_status` bigint NOT NULL default '0',

    PRIMARY KEY  (`id`),
    KEY `widget_id` (`widget_id`),
    KEY `type` (`type`),
    KEY `cms_block_id` (`cms_block_id`),
    KEY `product_id` (`product_id`),
    KEY `position` (`position`),
    KEY `edit_session_id` (`edit_session_id`),
    KEY `edit_status` (`edit_status`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
  
  ALTER TABLE `{$this->getTable($table)}`
  	  ADD CONSTRAINT `FK_{$this->getTable($table)}_widget/widget_instance` FOREIGN KEY (`widget_id`)
  	  REFERENCES `{$installer->getTable('widget/widget_instance')}` (`instance_id`) ON DELETE CASCADE ON UPDATE CASCADE;
  	  
  ALTER TABLE `{$this->getTable($table)}`
  	  ADD CONSTRAINT `FK_{$this->getTable($table)}_cms/block` FOREIGN KEY (`cms_block_id`)
  	  REFERENCES `{$installer->getTable('cms/block')}` (`block_id`) ON DELETE CASCADE ON UPDATE CASCADE;

  ALTER TABLE `{$this->getTable($table)}`
  	  ADD CONSTRAINT `FK_{$this->getTable($table)}_catalog/product` FOREIGN KEY (`product_id`)
  	  REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

  ALTER TABLE `{$this->getTable($table)}`
  	  ADD CONSTRAINT `FK_{$this->getTable($table)}_mana_db/edit_session` FOREIGN KEY (`edit_session_id`)
  	  REFERENCES `{$installer->getTable('mana_db/edit_session')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
");
if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();
