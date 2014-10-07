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

// page: global custom settings
$table = $this->getTable('mana_content/page_globalCustomSettings');
$installer->run("
    CREATE TABLE `$table` (
        `id` bigint(20) NOT NULL AUTO_INCREMENT,

        `parent_id` bigint(20) NULL,
        `redirect_id` bigint(20) NULL,

        `default_mask0` int(10) unsigned NOT NULL,
        `default_mask1` int(10) unsigned NOT NULL,
        `edit_session_id` bigint NOT NULL default '0',
        `edit_status` bigint NOT NULL default '0',

        `is_active` tinyint NOT NULL DEFAULT '1',
        `url_key` varchar(128) NOT NULL DEFAULT '',
        `title` varchar(255) NOT NULL DEFAULT '',

        `content` mediumtext NOT NULL,
        `page_layout` varchar(128) NOT NULL DEFAULT '',
        `layout_xml` mediumtext NOT NULL,
        `custom_design_active_from` datetime DEFAULT NULL,
        `custom_design_active_to` datetime DEFAULT NULL,
        `custom_design` varchar(128) NOT NULL DEFAULT '',
        `custom_layout_xml` mediumtext NOT NULL,
        `meta_title` varchar(255) NOT NULL DEFAULT '',
        `meta_keywords` mediumtext NOT NULL,
        `meta_description` mediumtext NOT NULL,
        `position` int(10) unsigned NOT NULL,
        `level` int(10) unsigned NOT NULL,

        PRIMARY KEY (`id`),
        KEY `parent_id` (`parent_id`),
        KEY `redirect_id` (`redirect_id`),
        KEY `edit_session_id` (`edit_session_id`),
        KEY `edit_status` (`edit_status`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_parent` FOREIGN KEY (`parent_id`)
            REFERENCES `{$this->getTable('mana_content/page_globalCustomSettings')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_redirect` FOREIGN KEY (`redirect_id`)
            REFERENCES `{$this->getTable('mana_content/page_globalCustomSettings')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$this->getTable($table)}_edit` FOREIGN KEY (`edit_session_id`)
            REFERENCES `{$this->getTable('mana_db/edit_session')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

// page: global final settings
$table = $this->getTable('mana_content/page_global');
$installer->run("
    CREATE TABLE `$table` (
        `id` bigint(20) NOT NULL AUTO_INCREMENT,
        `page_global_custom_settings_id` bigint(20) NOT NULL,

        `edit_session_id` bigint NOT NULL default '0',
        `edit_status` bigint NOT NULL default '0',
        `url_key` varchar(128) NOT NULL,

        PRIMARY KEY (`id`),
        UNIQUE KEY `page_global_custom_settings_id` (`page_global_custom_settings_id`),
        KEY `edit_session_id` (`edit_session_id`),
        KEY `edit_status` (`edit_status`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_custom` FOREIGN KEY (`page_global_custom_settings_id`)
            REFERENCES `{$this->getTable('mana_content/page_globalCustomSettings')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$this->getTable($table)}_edit` FOREIGN KEY (`edit_session_id`)
            REFERENCES `{$this->getTable('mana_db/edit_session')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

// page: store custom settings
$table = $this->getTable('mana_content/page_storeCustomSettings');
$installer->run("
    CREATE TABLE `$table` (
        `id` bigint(20) NOT NULL AUTO_INCREMENT,
        `page_global_id` bigint(20) NOT NULL,
        `store_id` smallint(5) unsigned NOT NULL,

        `default_mask0` int(10) unsigned NOT NULL,
        `default_mask1` int(10) unsigned NOT NULL,
        `edit_session_id` bigint NOT NULL default '0',
        `edit_status` bigint NOT NULL default '0',

        `is_active` tinyint NOT NULL DEFAULT '1',
        `url_key` varchar(128) NOT NULL DEFAULT '',
        `title` varchar(255) NOT NULL DEFAULT '',

        `content` mediumtext NOT NULL,
        `page_layout` varchar(128) NOT NULL DEFAULT '',
        `layout_xml` mediumtext NOT NULL,
        `custom_design_active_from` datetime DEFAULT NULL,
        `custom_design_active_to` datetime DEFAULT NULL,
        `custom_design` varchar(128) NOT NULL DEFAULT '',
        `custom_layout_xml` mediumtext NOT NULL,
        `meta_title` varchar(255) NOT NULL DEFAULT '',
        `meta_keywords` mediumtext NOT NULL,
        `meta_description` mediumtext NOT NULL,
        `position` int(10) unsigned DEFAULT '0',

        PRIMARY KEY (`id`),
        KEY `page_global_id` (`page_global_id`),
        KEY `store_id` (`store_id`),
        KEY `edit_session_id` (`edit_session_id`),
        KEY `edit_status` (`edit_status`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_page_g` FOREIGN KEY (`page_global_id`)
            REFERENCES `{$this->getTable('mana_content/page_global')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_store` FOREIGN KEY (`store_id`)
            REFERENCES `{$this->getTable('core/store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$this->getTable($table)}_edit` FOREIGN KEY (`edit_session_id`)
            REFERENCES `{$this->getTable('mana_db/edit_session')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

// page: store final settings
$table = $this->getTable('mana_content/page_store');
$installer->run("
    CREATE TABLE `$table` (
        `id` bigint(20) NOT NULL AUTO_INCREMENT,
        `page_global_id` bigint(20) NOT NULL,
        `store_id` smallint(5) unsigned NOT NULL,
        `page_store_custom_settings_id` bigint(20) DEFAULT NULL,

        `edit_session_id` bigint NOT NULL default '0',
        `edit_status` bigint NOT NULL default '0',

        `is_active` tinyint NOT NULL DEFAULT '1',
        `url_key` varchar(128) NOT NULL DEFAULT '',
        `title` varchar(255) NOT NULL DEFAULT '',

        `content` mediumtext NOT NULL,
        `page_layout` varchar(128) NOT NULL DEFAULT '',
        `layout_xml` mediumtext NOT NULL,
        `custom_design_active_from` datetime DEFAULT NULL,
        `custom_design_active_to` datetime DEFAULT NULL,
        `custom_design` varchar(128) NOT NULL DEFAULT '',
        `custom_layout_xml` mediumtext NOT NULL,
        `meta_title` varchar(255) NOT NULL DEFAULT '',
        `meta_keywords` mediumtext NOT NULL,
        `meta_description` mediumtext NOT NULL,
        `position` int(10) unsigned NOT NULL,
        `level` int(10) unsigned NOT NULL,

        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_key` (`page_global_id`,`store_id`),
        KEY `page_store_custom_settings_id` (`page_store_custom_settings_id`),
        KEY `page_global_id` (`page_global_id`),
        KEY `store_id` (`store_id`),
        KEY `edit_session_id` (`edit_session_id`),
        KEY `edit_status` (`edit_status`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_page_g` FOREIGN KEY (`page_global_id`)
            REFERENCES `{$this->getTable('mana_content/page_global')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_store` FOREIGN KEY (`store_id`)
            REFERENCES `{$this->getTable('core/store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_page_s` FOREIGN KEY (`page_store_custom_settings_id`)
            REFERENCES `{$this->getTable('mana_content/page_storeCustomSettings')}` (`id`)
            ON DELETE SET NULL ON UPDATE SET NULL,
        ADD CONSTRAINT `FK_{$this->getTable($table)}_edit` FOREIGN KEY (`edit_session_id`)
            REFERENCES `{$this->getTable('mana_db/edit_session')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");


// seo url: additional fields for deleting URL keys using referential integrity
$table = $this->getTable('mana_seo/url');
$installer->run(
    "
    ALTER TABLE `$table`
        ADD COLUMN `book_page_id` bigint(20) DEFAULT NULL,
        ADD KEY `book_page_id` (`book_page_id`),
        ADD CONSTRAINT `FK_{$table}_book_page_s` FOREIGN KEY (`book_page_id`)
            REFERENCES `{$this->getTable('mana_content/page_store')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
"
);

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();

