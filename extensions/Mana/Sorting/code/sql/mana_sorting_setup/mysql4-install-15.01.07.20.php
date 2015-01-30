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
      `default_mask0` int(10) unsigned NOT NULL,
      `attribute_id_0` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_1` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_2` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_3` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_4` smallint(5) unsigned DEFAULT NULL,
      `sorting_method_0` varchar(255) DEFAULT NULL,
      `sorting_method_1` varchar(255) DEFAULT NULL,
      `sorting_method_2` varchar(255) DEFAULT NULL,
      `sorting_method_3` varchar(255) DEFAULT NULL,
      `sorting_method_4` varchar(255) DEFAULT NULL,
      `attribute_id_0_sortdir` tinyint DEFAULT '1',
      `attribute_id_1_sortdir` tinyint DEFAULT '1',
      `attribute_id_2_sortdir` tinyint DEFAULT '1',
      `attribute_id_3_sortdir` tinyint DEFAULT '1',
      `attribute_id_4_sortdir` tinyint DEFAULT '1',
      `title` varchar(255) NOT NULL,
      `position` int(11) NOT NULL,
      `is_active` tinyint NOT NULL DEFAULT '1',
      `url_key` varchar(255) NOT NULL,

      PRIMARY KEY (`id`),
      KEY `attribute_id_0` (`attribute_id_0`),
      KEY `attribute_id_1` (`attribute_id_1`),
      KEY `attribute_id_2` (`attribute_id_2`),
      KEY `attribute_id_3` (`attribute_id_3`),
      KEY `attribute_id_4` (`attribute_id_4`)
    )
    ENGINE = INNODB DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_attr_0` FOREIGN KEY (`attribute_id_0`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_1` FOREIGN KEY (`attribute_id_1`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_2` FOREIGN KEY (`attribute_id_2`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_3` FOREIGN KEY (`attribute_id_3`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_4` FOREIGN KEY (`attribute_id_4`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

$table = $this->getTable('mana_sorting/method_storeCustomSettings');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `method_id` bigint(20) NOT NULL,
      `store_id` smallint(5) unsigned NOT NULL,
      `default_mask0` int(10) unsigned NOT NULL,
      `attribute_id_0` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_1` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_2` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_3` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_4` smallint(5) unsigned DEFAULT NULL,
      `sorting_method_0` varchar(255) DEFAULT NULL,
      `sorting_method_1` varchar(255) DEFAULT NULL,
      `sorting_method_2` varchar(255) DEFAULT NULL,
      `sorting_method_3` varchar(255) DEFAULT NULL,
      `sorting_method_4` varchar(255) DEFAULT NULL,
      `attribute_id_0_sortdir` tinyint DEFAULT '1',
      `attribute_id_1_sortdir` tinyint DEFAULT '1',
      `attribute_id_2_sortdir` tinyint DEFAULT '1',
      `attribute_id_3_sortdir` tinyint DEFAULT '1',
      `attribute_id_4_sortdir` tinyint DEFAULT '1',
      `title` varchar(255) DEFAULT NULL,
      `position` int(11) DEFAULT NULL,
      `is_active` tinyint DEFAULT NULL,
      `url_key` varchar(255) DEFAULT NULL,

      PRIMARY KEY (`id`),
      KEY(`store_id`),
      KEY `attribute_id_0` (`attribute_id_0`),
      KEY `attribute_id_1` (`attribute_id_1`),
      KEY `attribute_id_2` (`attribute_id_2`),
      KEY `attribute_id_3` (`attribute_id_3`),
      KEY `attribute_id_4` (`attribute_id_4`)

    )
    ENGINE = INNODB DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_method` FOREIGN KEY (`method_id`)
            REFERENCES `{$this->getTable('mana_sorting/method')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_store` FOREIGN KEY (`store_id`)
            REFERENCES `{$this->getTable('core/store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_0` FOREIGN KEY (`attribute_id_0`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_1` FOREIGN KEY (`attribute_id_1`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_2` FOREIGN KEY (`attribute_id_2`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_3` FOREIGN KEY (`attribute_id_3`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_4` FOREIGN KEY (`attribute_id_4`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");


$table = $this->getTable('mana_sorting/method_store');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `method_id` bigint(20) NOT NULL,
      `store_id` smallint(5) unsigned NOT NULL,
      `method_store_custom_settings_id` bigint(20) NULL,
      `attribute_id_0` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_1` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_2` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_3` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_4` smallint(5) unsigned DEFAULT NULL,
      `sorting_method_0` varchar(255) DEFAULT NULL,
      `sorting_method_1` varchar(255) DEFAULT NULL,
      `sorting_method_2` varchar(255) DEFAULT NULL,
      `sorting_method_3` varchar(255) DEFAULT NULL,
      `sorting_method_4` varchar(255) DEFAULT NULL,
      `attribute_id_0_sortdir` tinyint DEFAULT '1',
      `attribute_id_1_sortdir` tinyint DEFAULT '1',
      `attribute_id_2_sortdir` tinyint DEFAULT '1',
      `attribute_id_3_sortdir` tinyint DEFAULT '1',
      `attribute_id_4_sortdir` tinyint DEFAULT '1',
      `title` varchar(255) NOT NULL,
      `position` int(11) NOT NULL,
      `is_active` tinyint NOT NULL DEFAULT '1',
      `url_key` varchar(255) NOT NULL,

      PRIMARY KEY (`id`),
        UNIQUE KEY `unique_key` (`method_id`, `store_id`),
        KEY(`method_id`),
        KEY(`store_id`),
        KEY(`method_store_custom_settings_id`),

      KEY `attribute_id_0` (`attribute_id_0`),
      KEY `attribute_id_1` (`attribute_id_1`),
      KEY `attribute_id_2` (`attribute_id_2`),
      KEY `attribute_id_3` (`attribute_id_3`),
      KEY `attribute_id_4` (`attribute_id_4`)
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
            ON DELETE SET NULL ON UPDATE SET NULL,
        ADD CONSTRAINT `FK_{$table}_attr_0` FOREIGN KEY (`attribute_id_0`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_1` FOREIGN KEY (`attribute_id_1`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_2` FOREIGN KEY (`attribute_id_2`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_3` FOREIGN KEY (`attribute_id_3`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_4` FOREIGN KEY (`attribute_id_4`)
            REFERENCES `{$this->getTable('eav/attribute')}` (`attribute_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();

