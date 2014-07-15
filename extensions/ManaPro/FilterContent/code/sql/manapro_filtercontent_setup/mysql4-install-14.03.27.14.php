<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
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

$table = $this->getTable('manapro_filtercontent/rule_globalCustomSettings');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,

      `default_mask0` int(10) unsigned NOT NULL,
      `default_mask1` int(10) unsigned NOT NULL,

      `priority`  int(10) NOT NULL DEFAULT '0',
      `is_active` tinyint NOT NULL DEFAULT '0',
      `stop_further_processing` tinyint NOT NULL DEFAULT '0',

      `conditions` mediumtext NOT NULL,
      `common_directives` mediumtext NOT NULL,

      `meta_title` mediumtext NOT NULL,
      `meta_keywords` mediumtext NOT NULL,
      `meta_description` mediumtext NOT NULL,
      `meta_robots` mediumtext NOT NULL,

      `title` mediumtext NOT NULL,
      `subtitle` mediumtext NOT NULL,
      `description` mediumtext NOT NULL,
      `additional_description` mediumtext NOT NULL,

      `layout_xml` mediumtext NOT NULL,
      `widget_layout_xml` mediumtext NOT NULL,

      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$table = $this->getTable('manapro_filtercontent/rule_global');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `filter_content_global_custom_settings_id` bigint NOT NULL,

      `priority`  int(10) NOT NULL DEFAULT '0',
      `is_active` tinyint NOT NULL DEFAULT '0',
      `stop_further_processing` tinyint NOT NULL DEFAULT '0',

      `conditions` mediumtext NOT NULL,
      `common_directives` mediumtext NOT NULL,

      `meta_title` mediumtext NOT NULL,
      `meta_keywords` mediumtext NOT NULL,
      `meta_description` mediumtext NOT NULL,
      `meta_robots` mediumtext NOT NULL,

      `title` mediumtext NOT NULL,
      `subtitle` mediumtext NOT NULL,
      `description` mediumtext NOT NULL,
      `additional_description` mediumtext NOT NULL,

      `layout_xml` mediumtext NOT NULL,
      `widget_layout_xml` mediumtext NOT NULL,

      PRIMARY KEY (`id`),
      UNIQUE KEY `filter_content_global_custom_settings_id` (`filter_content_global_custom_settings_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_custom` FOREIGN KEY (`filter_content_global_custom_settings_id`)
            REFERENCES `{$this->getTable('manapro_filtercontent/rule_globalCustomSettings')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

$table = $this->getTable('manapro_filtercontent/rule_storeCustomSettings');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `filter_content_global_id` bigint NOT NULL,
      `store_id` smallint(5) unsigned NOT NULL,

      `default_mask0` int(10) unsigned NOT NULL,
      `default_mask1` int(10) unsigned NOT NULL,

      `priority`  int(10) NOT NULL DEFAULT '0',
      `is_active` tinyint NOT NULL DEFAULT '0',
      `stop_further_processing` tinyint NOT NULL DEFAULT '0',

      `common_directives` mediumtext NOT NULL,

      `meta_title` mediumtext NOT NULL,
      `meta_keywords` mediumtext NOT NULL,
      `meta_description` mediumtext NOT NULL,
      `meta_robots` mediumtext NOT NULL,

      `title` mediumtext NOT NULL,
      `subtitle` mediumtext NOT NULL,
      `description` mediumtext NOT NULL,
      `additional_description` mediumtext NOT NULL,

      `layout_xml` mediumtext NOT NULL,
      `widget_layout_xml` mediumtext NOT NULL,

      PRIMARY KEY (`id`),
      KEY `filter_content_global_id` (`filter_content_global_id`),
      KEY `store_id` (`store_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_global` FOREIGN KEY (`filter_content_global_id`)
            REFERENCES `{$this->getTable('manapro_filtercontent/rule_global')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_store` FOREIGN KEY (`store_id`)
            REFERENCES `{$this->getTable('core/store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

$table = $this->getTable('manapro_filtercontent/rule_store');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `filter_content_global_id` bigint NOT NULL,
      `store_id` smallint(5) unsigned NOT NULL,
      `filter_content_store_custom_settings_id` bigint NULL,

      `priority`  int(10) NOT NULL DEFAULT '0',
      `is_active` tinyint NOT NULL DEFAULT '0',
      `stop_further_processing` tinyint NOT NULL DEFAULT '0',

      `conditions` mediumtext NOT NULL,
      `common_directives` mediumtext NOT NULL,

      `meta_title` mediumtext NOT NULL,
      `meta_keywords` mediumtext NOT NULL,
      `meta_description` mediumtext NOT NULL,
      `meta_robots` mediumtext NOT NULL,

      `title` mediumtext NOT NULL,
      `subtitle` mediumtext NOT NULL,
      `description` mediumtext NOT NULL,
      `additional_description` mediumtext NOT NULL,

      `layout_xml` mediumtext NOT NULL,
      `widget_layout_xml` mediumtext NOT NULL,

      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_key` (`filter_content_global_id`,`store_id`),
      KEY `filter_content_store_custom_settings_id` (`filter_content_store_custom_settings_id`),
      KEY `filter_content_global_id` (`filter_content_global_id`),
      KEY `store_id` (`store_id`),
      KEY `priority` (`priority`),
      KEY `is_active` (`is_active`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_global` FOREIGN KEY (`filter_content_global_id`)
            REFERENCES `{$this->getTable('manapro_filtercontent/rule_global')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_custom` FOREIGN KEY (`filter_content_store_custom_settings_id`)
            REFERENCES `{$this->getTable('manapro_filtercontent/rule_storeCustomSettings')}` (`id`)
            ON DELETE SET NULL ON UPDATE SET NULL,
        ADD CONSTRAINT `FK_{$table}_store` FOREIGN KEY (`store_id`)
            REFERENCES `{$this->getTable('core/store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

$table = $this->getTable('manapro_filtercontent/rule_condition');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `filter_content_global_custom_settings_id` bigint NOT NULL,

      `page_type` varchar(20) NULL,
      `category_page_id` int(10) unsigned NULL,
      `cms_page_id` smallint(6) NULL,
      `option_page_global_id` bigint NULL,

      `filter_global_id` bigint NULL,
      `option_id` int(10) unsigned NULL,
      `category_id` int(10) unsigned NULL,

      PRIMARY KEY (`id`),
      KEY `filter_content_global_custom_settings_id` (`filter_content_global_custom_settings_id`),
      KEY `page_type` (`page_type`),
      KEY `category_page_id` (`category_page_id`),
      KEY `cms_page_id` (`cms_page_id`),
      KEY `option_page_global_id` (`option_page_global_id`),
      KEY `filter_global_id` (`filter_global_id`),
      KEY `option_id` (`option_id`),
      KEY `category_id` (`category_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_rule` FOREIGN KEY (`filter_content_global_custom_settings_id`)
            REFERENCES `{$this->getTable('manapro_filtercontent/rule_globalCustomSettings')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_cat_page` FOREIGN KEY (`category_page_id`)
            REFERENCES `{$this->getTable('catalog/category')}` (`entity_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_cms_page` FOREIGN KEY (`cms_page_id`)
            REFERENCES `{$this->getTable('cms/page')}` (`page_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_filter` FOREIGN KEY (`filter_global_id`)
            REFERENCES `{$this->getTable('mana_filters/filter2')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_option` FOREIGN KEY (`option_id`)
            REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_cat` FOREIGN KEY (`category_id`)
            REFERENCES `{$this->getTable('catalog/category')}` (`entity_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

/*
        ADD CONSTRAINT `FK_{$table}_opt_page` FOREIGN KEY (`option_page_global_id`)
            REFERENCES `{$this->getTable('mana_attributepage/optionPage_global')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,

*/

$filterValueTables = array(
    $this->getTable('mana_filters/filter2_value'),
    $this->getTable('mana_filters/filter2_value_store'),
);

foreach ($filterValueTables as $table) {
    $installer->run("
        ALTER TABLE `$table`
            ADD COLUMN (`content_is_active` tinyint NOT NULL DEFAULT '0'),
            ADD COLUMN (`content_is_initialized` tinyint NOT NULL DEFAULT '0'),
            ADD COLUMN (`content_priority`  int(10) NOT NULL DEFAULT '0'),
            ADD COLUMN (`content_stop_further_processing` tinyint NOT NULL DEFAULT '0'),
            ADD COLUMN (`content_common_directives` mediumtext NOT NULL),

            ADD COLUMN (`content_meta_title` mediumtext NOT NULL),
            ADD COLUMN (`content_meta_keywords` mediumtext NOT NULL),
            ADD COLUMN (`content_meta_description` mediumtext NOT NULL),
            ADD COLUMN (`content_meta_robots` mediumtext NOT NULL),

            ADD COLUMN (`content_title` mediumtext NOT NULL),
            ADD COLUMN (`content_subtitle` mediumtext NOT NULL),
            ADD COLUMN (`content_description` mediumtext NOT NULL),
            ADD COLUMN (`content_additional_description` mediumtext NOT NULL),

            ADD COLUMN (`content_layout_xml` mediumtext NOT NULL),
            ADD COLUMN (`content_widget_layout_xml` mediumtext NOT NULL);
    ");
}

/*
$optionPageTables = array(
    $this->getTable('mana_attributepage/optionPage_globalCustomSettings'),
    $this->getTable('mana_attributepage/optionPage_global'),
    $this->getTable('mana_attributepage/optionPage_storeCustomSettings'),
    $this->getTable('mana_attributepage/optionPage_store'),
);

foreach ($optionPageTables as $table) {
    $installer->run("
        ALTER TABLE `$table`
            ADD COLUMN (`content_is_active` tinyint NOT NULL DEFAULT '0'),
            ADD COLUMN (`content_stop_further_processing` tinyint NOT NULL DEFAULT '0'),

            ADD COLUMN (`content_meta_title` varchar(255) NOT NULL DEFAULT ''),
            ADD COLUMN (`content_meta_keywords` mediumtext NOT NULL),
            ADD COLUMN (`content_meta_description` mediumtext NOT NULL),
            ADD COLUMN (`content_meta_robots` varchar(255) NOT NULL DEFAULT ''),

            ADD COLUMN (`content_title` varchar(255) NOT NULL DEFAULT ''),
            ADD COLUMN (`content_subtitle` varchar(255) NOT NULL DEFAULT ''),
            ADD COLUMN (`content_description` mediumtext NOT NULL),
            ADD COLUMN (`content_additional_description` mediumtext NOT NULL),

            ADD COLUMN (`content_layout_xml` mediumtext NOT NULL),
            ADD COLUMN (`content_widget_layout_xml` mediumtext NOT NULL);
    ");
}
*/

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();

