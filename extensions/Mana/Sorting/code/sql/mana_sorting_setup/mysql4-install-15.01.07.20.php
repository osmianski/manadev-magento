<?php
/** 
 * @category    Mana
 * @package     Mana_Content
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

$table = $this->getTable('mana_sorting/method');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `position` int(11) NOT NULL,
      `is_active` tinyint NOT NULL DEFAULT '1',
      PRIMARY KEY (`id`)
    )
    ENGINE = INNODB DEFAULT CHARSET=utf8;
");

$table = $this->getTable('mana_sorting/method_storeCustomSettings');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `method_id` bigint(20) NOT NULL,
      `store_id` smallint(5) unsigned NOT NULL,
      `default_mask0` int(10) unsigned NOT NULL,
      `title` varchar(255) NOT NULL,
      `position` int(11) NOT NULL,
      `is_active` tinyint NOT NULL DEFAULT '1',

      PRIMARY KEY (`id`),
      KEY(`store_id`)

    )
    ENGINE = INNODB DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_method` FOREIGN KEY (`method_id`)
            REFERENCES `{$this->getTable('mana_sorting/method')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_store` FOREIGN KEY (`store_id`)
            REFERENCES `{$this->getTable('core/store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");


$table = $this->getTable('mana_sorting/method_store');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `method_id` bigint(20) NOT NULL,
      `store_id` smallint(5) unsigned NOT NULL,
      `method_store_custom_settings_id` bigint(20) NULL,
      `title` varchar(255) NOT NULL,
      `position` int(11) NOT NULL,
      `is_active` tinyint NOT NULL DEFAULT '1',

      PRIMARY KEY (`id`),
        UNIQUE KEY `unique_key` (`method_id`, `store_id`),
        KEY(`method_id`),
        KEY(`store_id`),
        KEY(`method_store_custom_settings_id`)
    )
    ENGINE = INNODB DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_method` FOREIGN KEY (`method_id`)
            REFERENCES `{$this->getTable('mana_sorting/method')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_store` FOREIGN KEY (`store_id`)
            REFERENCES `{$this->getTable('core/store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_method_s` FOREIGN KEY (`method_store_custom_settings_id`)
            REFERENCES `{$this->getTable('mana_sorting/method_storeCustomSettings')}` (`id`)
            ON DELETE SET NULL ON UPDATE SET NULL;
");

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();

