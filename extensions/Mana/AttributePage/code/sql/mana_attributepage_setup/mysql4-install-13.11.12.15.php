<?php
/** 
 * @category    Mana
 * @package     Mana_Menu
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

// attribute page: global custom settings
$table = $this->getTable('mana_attributepage/attributePage_globalCustomSettings');
$installer->run(
    "
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `attribute_id_0` smallint(5) unsigned NOT NULL,
      `attribute_id_1` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_2` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_3` smallint(5) unsigned DEFAULT NULL,
      `attribute_id_4` smallint(5) unsigned DEFAULT NULL,

      `default_mask0` int(10) unsigned NOT NULL,
      `default_mask1` int(10) unsigned NOT NULL,
      `default_mask2` int(10) unsigned NOT NULL,
      `default_mask3` int(10) unsigned NOT NULL,

      `is_active` tinyint NOT NULL DEFAULT '1',
      `title` varchar(255) NOT NULL DEFAULT '',
      `description` mediumtext NOT NULL,
      `image` varchar(255) NOT NULL DEFAULT '',
      `image_width` int(10) NULL,
      `image_height` int(10) NULL,
      `include_in_menu` tinyint NOT NULL DEFAULT '1',
      `url_key` varchar(128) NOT NULL DEFAULT '',
      `template` varchar(20) NOT NULL DEFAULT '',
      `show_alphabetic_search` tinyint NOT NULL DEFAULT '1',
      `show_featured_options` tinyint NOT NULL DEFAULT '1',
      `column_count` int(10) NOT NULL DEFAULT '4',
      `page_layout` varchar(128) NOT NULL DEFAULT '',
      `layout_xml` mediumtext NOT NULL,
      `custom_design_active_from` datetime DEFAULT NULL,
      `custom_design_active_to` datetime DEFAULT NULL,
      `custom_design` varchar(128) NOT NULL DEFAULT '',
      `custom_layout_xml` mediumtext NOT NULL,
      `meta_title` varchar(255) NOT NULL DEFAULT '',
      `meta_keywords` mediumtext NOT NULL,
      `meta_description` mediumtext NOT NULL,

      `option_page_include_filter_name` tinyint NOT NULL DEFAULT '0',
      `option_page_image` varchar(255) NOT NULL DEFAULT '',
      `option_page_image_width` int(10) NULL,
      `option_page_image_height` int(10) NULL,
      `option_page_featured_image_width` int(10) NULL,
      `option_page_featured_image_height` int(10) NULL,
      `option_page_product_image_width` int(10) NULL,
      `option_page_product_image_height` int(10) NULL,
      `option_page_sidebar_image_width` int(10) NULL,
      `option_page_sidebar_image_height` int(10) NULL,
      `option_page_include_in_menu` tinyint NOT NULL DEFAULT '1',
      `option_page_is_active` tinyint NOT NULL DEFAULT '1',
      `option_page_is_featured` tinyint NOT NULL DEFAULT '0',
      `option_page_show_products` tinyint NOT NULL DEFAULT '1',
      `option_page_available_sort_by` mediumtext NOT NULL,
      `option_page_default_sort_by` varchar(128) NOT NULL DEFAULT '',
      `option_page_price_step` decimal(12,4) DEFAULT NULL,
      `option_page_page_layout` varchar(128) NOT NULL DEFAULT '',
      `option_page_layout_xml` mediumtext NOT NULL,
      `option_page_custom_design_active_from` datetime DEFAULT NULL,
      `option_page_custom_design_active_to` datetime DEFAULT NULL,
      `option_page_custom_design` varchar(128) NOT NULL DEFAULT '',
      `option_page_custom_layout_xml` mediumtext NOT NULL,

      PRIMARY KEY (`id`),
      KEY `attribute_id_0` (`attribute_id_0`),
      KEY `attribute_id_1` (`attribute_id_1`),
      KEY `attribute_id_2` (`attribute_id_2`),
      KEY `attribute_id_3` (`attribute_id_3`),
      KEY `attribute_id_4` (`attribute_id_4`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
"
);

// attribute page: global final settings
$table = $this->getTable('mana_attributepage/attributePage_global');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `attribute_page_global_custom_settings_id` bigint(20) NOT NULL,

      `all_attribute_ids` varchar(255) NOT NULL DEFAULT '',

      `title` varchar(255) NOT NULL DEFAULT '',
      `description` mediumtext NOT NULL,
      `url_key` varchar(128) NOT NULL DEFAULT '',
      `meta_title` varchar(255) NOT NULL DEFAULT '',
      `meta_keywords` mediumtext NOT NULL,
      `meta_description` mediumtext NOT NULL,

      PRIMARY KEY (`id`),
      UNIQUE KEY `all_attribute_ids` (`all_attribute_ids`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_custom` FOREIGN KEY (`attribute_page_global_custom_settings_id`)
            REFERENCES `{$this->getTable('mana_attributepage/attributePage_globalCustomSettings')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

// attribute page: store level custom settings
$table = $this->getTable('mana_attributepage/attributePage_storeCustomSettings');
$installer->run(
    "
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `attribute_page_global_id` bigint(20) NOT NULL,
      `store_id` smallint(5) unsigned NOT NULL,

      `default_mask0` int(10) unsigned NOT NULL,
      `default_mask1` int(10) unsigned NOT NULL,
      `default_mask2` int(10) unsigned NOT NULL,
      `default_mask3` int(10) unsigned NOT NULL,

      `is_active` tinyint NOT NULL DEFAULT '1',
      `title` varchar(255) NOT NULL DEFAULT '',
      `description` mediumtext NOT NULL,
      `image` varchar(255) NOT NULL DEFAULT '',
      `image_width` int(10) NULL,
      `image_height` int(10) NULL,
      `include_in_menu` tinyint NOT NULL DEFAULT '1',
      `url_key` varchar(128) NOT NULL DEFAULT '',
      `template` varchar(20) NOT NULL DEFAULT '',
      `show_alphabetic_search` tinyint NOT NULL DEFAULT '1',
      `show_featured_options` tinyint NOT NULL DEFAULT '1',
      `column_count` int(10) NOT NULL DEFAULT '4',
      `page_layout` varchar(128) NOT NULL DEFAULT '',
      `layout_xml` mediumtext NOT NULL,
      `custom_design_active_from` datetime DEFAULT NULL,
      `custom_design_active_to` datetime DEFAULT NULL,
      `custom_design` varchar(128) NOT NULL DEFAULT '',
      `custom_layout_xml` mediumtext NOT NULL,
      `meta_title` varchar(255) NOT NULL DEFAULT '',
      `meta_keywords` mediumtext NOT NULL,
      `meta_description` mediumtext NOT NULL,

      `option_page_include_filter_name` tinyint NOT NULL DEFAULT '0',
      `option_page_image` varchar(255) NOT NULL DEFAULT '',
      `option_page_image_width` int(10) NULL,
      `option_page_image_height` int(10) NULL,
      `option_page_featured_image_width` int(10) NULL,
      `option_page_featured_image_height` int(10) NULL,
      `option_page_product_image_width` int(10) NULL,
      `option_page_product_image_height` int(10) NULL,
      `option_page_sidebar_image_width` int(10) NULL,
      `option_page_sidebar_image_height` int(10) NULL,
      `option_page_include_in_menu` tinyint NOT NULL DEFAULT '1',
      `option_page_is_active` tinyint NOT NULL DEFAULT '1',
      `option_page_show_products` tinyint NOT NULL DEFAULT '1',
      `option_page_is_featured` tinyint NOT NULL DEFAULT '0',
      `option_page_available_sort_by` mediumtext NOT NULL,
      `option_page_default_sort_by` varchar(128) NOT NULL DEFAULT '',
      `option_page_price_step` decimal(12,4) DEFAULT NULL,
      `option_page_page_layout` varchar(128) NOT NULL DEFAULT '',
      `option_page_layout_xml` mediumtext NOT NULL,
      `option_page_custom_design_active_from` datetime DEFAULT NULL,
      `option_page_custom_design_active_to` datetime DEFAULT NULL,
      `option_page_custom_design` varchar(128) NOT NULL DEFAULT '',
      `option_page_custom_layout_xml` mediumtext NOT NULL,

      PRIMARY KEY (`id`),
      KEY `attribute_page_global_id` (`attribute_page_global_id`),
      KEY `store_id` (`store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_attr_page_g` FOREIGN KEY (`attribute_page_global_id`)
            REFERENCES `{$this->getTable('mana_attributepage/attributePage_global')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_store` FOREIGN KEY (`store_id`)
            REFERENCES `{$this->getTable('core/store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
"
);

// attribute page: store-level final settings
$table = $this->getTable('mana_attributepage/attributePage_store');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `attribute_page_global_id` bigint(20) NOT NULL,
      `store_id` smallint(5) unsigned NOT NULL,
      `attribute_page_store_custom_settings_id` bigint(20) DEFAULT NULL,

      `is_active` tinyint NOT NULL DEFAULT '1',
      `title` varchar(255) NOT NULL DEFAULT '',
      `description` mediumtext NOT NULL,
      `image` varchar(255) NOT NULL DEFAULT '',
      `image_width` int(10) NULL,
      `image_height` int(10) NULL,
      `include_in_menu` tinyint NOT NULL DEFAULT '1',
      `url_key` varchar(128) NOT NULL DEFAULT '',
      `template` varchar(20) NOT NULL DEFAULT '',
      `show_alphabetic_search` tinyint NOT NULL DEFAULT '1',
      `show_featured_options` tinyint NOT NULL DEFAULT '1',
      `column_count` int(10) NOT NULL DEFAULT '4',
      `page_layout` varchar(128) NOT NULL DEFAULT '',
      `layout_xml` mediumtext NOT NULL,
      `custom_design_active_from` datetime DEFAULT NULL,
      `custom_design_active_to` datetime DEFAULT NULL,
      `custom_design` varchar(128) NOT NULL DEFAULT '',
      `custom_layout_xml` mediumtext NOT NULL,
      `meta_title` varchar(255) NOT NULL DEFAULT '',
      `meta_keywords` mediumtext NOT NULL,
      `meta_description` mediumtext NOT NULL,

      `option_page_include_filter_name` tinyint NOT NULL DEFAULT '0',
      `option_page_image` varchar(255) NOT NULL DEFAULT '',
      `option_page_image_width` int(10) NULL,
      `option_page_image_height` int(10) NULL,
      `option_page_featured_image_width` int(10) NULL,
      `option_page_featured_image_height` int(10) NULL,
      `option_page_product_image_width` int(10) NULL,
      `option_page_product_image_height` int(10) NULL,
      `option_page_sidebar_image_width` int(10) NULL,
      `option_page_sidebar_image_height` int(10) NULL,
      `option_page_include_in_menu` tinyint NOT NULL DEFAULT '1',
      `option_page_is_active` tinyint NOT NULL DEFAULT '1',
      `option_page_show_products` tinyint NOT NULL DEFAULT '1',
      `option_page_is_featured` tinyint NOT NULL DEFAULT '0',
      `option_page_available_sort_by` mediumtext NOT NULL,
      `option_page_default_sort_by` varchar(128) NOT NULL DEFAULT '',
      `option_page_price_step` decimal(12,4) DEFAULT NULL,
      `option_page_page_layout` varchar(128) NOT NULL DEFAULT '',
      `option_page_layout_xml` mediumtext NOT NULL,
      `option_page_custom_design_active_from` datetime DEFAULT NULL,
      `option_page_custom_design_active_to` datetime DEFAULT NULL,
      `option_page_custom_design` varchar(128) NOT NULL DEFAULT '',
      `option_page_custom_layout_xml` mediumtext NOT NULL,

      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_key` (`attribute_page_global_id`,`store_id`),
      KEY `attribute_page_store_custom_settings_id` (`attribute_page_store_custom_settings_id`),
      KEY `attribute_page_global_id` (`attribute_page_global_id`),
      KEY `store_id` (`store_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_attr_page_g` FOREIGN KEY (`attribute_page_global_id`)
            REFERENCES `{$this->getTable('mana_attributepage/attributePage_global')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_store` FOREIGN KEY (`store_id`)
            REFERENCES `{$this->getTable('core/store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_attr_page_s` FOREIGN KEY (`attribute_page_store_custom_settings_id`)
            REFERENCES `{$this->getTable('mana_attributepage/attributePage_storeCustomSettings')}` (`id`)
            ON DELETE SET NULL ON UPDATE SET NULL;
");

// option page: global custom settings
$table = $this->getTable('mana_attributepage/optionPage_globalCustomSettings');
$installer->run(
    "
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `attribute_page_global_id` bigint(20) NOT NULL,
      `option_id_0` int(10) unsigned NOT NULL,
      `option_id_1` int(10) unsigned DEFAULT NULL,
      `option_id_2` int(10) unsigned DEFAULT NULL,
      `option_id_3` int(10) unsigned DEFAULT NULL,
      `option_id_4` int(10) unsigned DEFAULT NULL,

      `default_mask0` int(10) unsigned NOT NULL,
      `default_mask1` int(10) unsigned NOT NULL,
      `default_mask2` int(10) unsigned NOT NULL,
      `default_mask3` int(10) unsigned NOT NULL,

      `is_active` tinyint NOT NULL DEFAULT '1',
      `title` varchar(255) NOT NULL DEFAULT '',
      `description` mediumtext NOT NULL,
      `image` varchar(255) NOT NULL DEFAULT '',
      `image_width` int(10) NULL,
      `image_height` int(10) NULL,
      `featured_image` varchar(255) NOT NULL DEFAULT '',
      `featured_image_width` int(10) NULL,
      `featured_image_height` int(10) NULL,
      `product_image` varchar(255) NOT NULL DEFAULT '',
      `product_image_width` int(10) NULL,
      `product_image_height` int(10) NULL,
      `sidebar_image` varchar(255) NOT NULL DEFAULT '',
      `sidebar_image_width` int(10) NULL,
      `sidebar_image_height` int(10) NULL,
      `include_in_menu` tinyint NOT NULL DEFAULT '1',
      `url_key` varchar(128) NOT NULL DEFAULT '',
      `show_products` tinyint NOT NULL DEFAULT '1',
      `is_featured` tinyint NOT NULL DEFAULT '0',
      `available_sort_by` mediumtext NOT NULL,
      `default_sort_by` varchar(128) NOT NULL DEFAULT '',
      `price_step` decimal(12,4) DEFAULT NULL,
      `page_layout` varchar(128) NOT NULL DEFAULT '',
      `layout_xml` mediumtext NOT NULL,
      `custom_design_active_from` datetime DEFAULT NULL,
      `custom_design_active_to` datetime DEFAULT NULL,
      `custom_design` varchar(128) NOT NULL DEFAULT '',
      `custom_layout_xml` mediumtext NOT NULL,
      `meta_title` varchar(255) NOT NULL DEFAULT '',
      `meta_keywords` mediumtext NOT NULL,
      `meta_description` mediumtext NOT NULL,

      PRIMARY KEY (`id`),
      KEY `attribute_page_global_id` (`attribute_page_global_id`),
      KEY `option_id_0` (`option_id_0`),
      KEY `option_id_1` (`option_id_1`),
      KEY `option_id_2` (`option_id_2`),
      KEY `option_id_3` (`option_id_3`),
      KEY `option_id_4` (`option_id_4`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_attr_page_g` FOREIGN KEY (`attribute_page_global_id`)
            REFERENCES `{$this->getTable('mana_attributepage/attributePage_global')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_option_0` FOREIGN KEY (`option_id_0`)
            REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_option_1` FOREIGN KEY (`option_id_1`)
            REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_option_2` FOREIGN KEY (`option_id_2`)
            REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_option_3` FOREIGN KEY (`option_id_3`)
            REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_option_4` FOREIGN KEY (`option_id_4`)
            REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
"
);

// option page: global final settings
$table = $this->getTable('mana_attributepage/optionPage_global');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `option_page_global_custom_settings_id` bigint(20) DEFAULT NULL,
      `attribute_page_global_id` bigint(20) NOT NULL,
      `option_id_0` int(10) unsigned NOT NULL,
      `option_id_1` int(10) unsigned DEFAULT NULL,
      `option_id_2` int(10) unsigned DEFAULT NULL,
      `option_id_3` int(10) unsigned DEFAULT NULL,
      `option_id_4` int(10) unsigned DEFAULT NULL,
      `all_option_ids` varchar(255) NOT NULL DEFAULT '',

      `title` varchar(255) NOT NULL DEFAULT '',
      `description` mediumtext NOT NULL,
      `image` varchar(255) NOT NULL DEFAULT '',
      `image_width` int(10) NULL,
      `image_height` int(10) NULL,
      `featured_image` varchar(255) NOT NULL DEFAULT '',
      `featured_image_width` int(10) NULL,
      `featured_image_height` int(10) NULL,
      `product_image` varchar(255) NOT NULL DEFAULT '',
      `product_image_width` int(10) NULL,
      `product_image_height` int(10) NULL,
      `sidebar_image` varchar(255) NOT NULL DEFAULT '',
      `sidebar_image_width` int(10) NULL,
      `sidebar_image_height` int(10) NULL,
      `include_in_menu` tinyint NOT NULL DEFAULT '1',
      `url_key` varchar(128) NOT NULL DEFAULT '',
      `meta_title` varchar(255) NOT NULL DEFAULT '',
      `meta_keywords` mediumtext NOT NULL,
      `meta_description` mediumtext NOT NULL,
      `is_active` tinyint NOT NULL DEFAULT '1',
      `show_products` tinyint NOT NULL DEFAULT '1',
      `is_featured` tinyint NOT NULL DEFAULT '0',
      `available_sort_by` mediumtext NOT NULL,
      `default_sort_by` varchar(128) NOT NULL DEFAULT '',
      `price_step` decimal(12,4) DEFAULT NULL,
      `page_layout` varchar(128) NOT NULL DEFAULT '',
      `layout_xml` mediumtext NOT NULL,
      `custom_design_active_from` datetime DEFAULT NULL,
      `custom_design_active_to` datetime DEFAULT NULL,
      `custom_design` varchar(128) NOT NULL DEFAULT '',
      `custom_layout_xml` mediumtext NOT NULL,

      PRIMARY KEY (`id`),
      KEY `attribute_page_global_id` (`attribute_page_global_id`),
      KEY `option_page_global_custom_settings_id` (`option_page_global_custom_settings_id`),
      KEY `option_id_0` (`option_id_0`),
      KEY `option_id_1` (`option_id_1`),
      KEY `option_id_2` (`option_id_2`),
      KEY `option_id_3` (`option_id_3`),
      KEY `option_id_4` (`option_id_4`),
      UNIQUE KEY `all_option_ids` (`attribute_page_global_id`,`all_option_ids`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_attr_page_g` FOREIGN KEY (`attribute_page_global_id`)
            REFERENCES `{$this->getTable('mana_attributepage/attributePage_global')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_opt_page_g` FOREIGN KEY (`option_page_global_custom_settings_id`)
            REFERENCES `{$this->getTable('mana_attributepage/optionPage_globalCustomSettings')}` (`id`)
            ON DELETE SET NULL ON UPDATE SET NULL,
        ADD CONSTRAINT `FK_{$table}_option_0` FOREIGN KEY (`option_id_0`)
            REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_option_1` FOREIGN KEY (`option_id_1`)
            REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_option_2` FOREIGN KEY (`option_id_2`)
            REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_option_3` FOREIGN KEY (`option_id_3`)
            REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_option_4` FOREIGN KEY (`option_id_4`)
            REFERENCES `{$this->getTable('eav/attribute_option')}` (`option_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

// option page: store-level custom settings
$table = $this->getTable('mana_attributepage/optionPage_storeCustomSettings');
$installer->run(
    "
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `option_page_global_id` bigint(20) NOT NULL,
      `store_id` smallint(5) unsigned NOT NULL,

      `default_mask0` int(10) unsigned NOT NULL,
      `default_mask1` int(10) unsigned NOT NULL,
      `default_mask2` int(10) unsigned NOT NULL,
      `default_mask3` int(10) unsigned NOT NULL,

      `is_active` tinyint NOT NULL DEFAULT '1',
      `title` varchar(255) NOT NULL DEFAULT '',
      `description` mediumtext NOT NULL,
      `image` varchar(255) NOT NULL DEFAULT '',
      `image_width` int(10) NULL,
      `image_height` int(10) NULL,
      `featured_image` varchar(255) NOT NULL DEFAULT '',
      `featured_image_width` int(10) NULL,
      `featured_image_height` int(10) NULL,
      `product_image` varchar(255) NOT NULL DEFAULT '',
      `product_image_width` int(10) NULL,
      `product_image_height` int(10) NULL,
      `sidebar_image` varchar(255) NOT NULL DEFAULT '',
      `sidebar_image_width` int(10) NULL,
      `sidebar_image_height` int(10) NULL,
      `include_in_menu` tinyint NOT NULL DEFAULT '1',
      `url_key` varchar(128) NOT NULL DEFAULT '',
      `show_products` tinyint NOT NULL DEFAULT '1',
      `is_featured` tinyint NOT NULL DEFAULT '0',
      `available_sort_by` mediumtext NOT NULL,
      `default_sort_by` varchar(128) NOT NULL DEFAULT '',
      `price_step` decimal(12,4) DEFAULT NULL,
      `page_layout` varchar(128) NOT NULL DEFAULT '',
      `layout_xml` mediumtext NOT NULL,
      `custom_design_active_from` datetime DEFAULT NULL,
      `custom_design_active_to` datetime DEFAULT NULL,
      `custom_design` varchar(128) NOT NULL DEFAULT '',
      `custom_layout_xml` mediumtext NOT NULL,
      `meta_title` varchar(255) NOT NULL DEFAULT '',
      `meta_keywords` mediumtext NOT NULL,
      `meta_description` mediumtext NOT NULL,

      PRIMARY KEY (`id`),
      KEY `option_page_global_id` (`option_page_global_id`),
      KEY `store_id` (`store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_opt_page_g` FOREIGN KEY (`option_page_global_id`)
            REFERENCES `{$this->getTable('mana_attributepage/optionPage_global')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_store` FOREIGN KEY (`store_id`)
            REFERENCES `{$this->getTable('core/store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
"
);

// option page: store-level final settings
$table = $this->getTable('mana_attributepage/optionPage_store');
$installer->run("
    CREATE TABLE `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `option_page_global_id` bigint(20) NOT NULL,
      `store_id` smallint(5) unsigned NOT NULL,
      `option_page_store_custom_settings_id` bigint(20) DEFAULT NULL,

      `is_active` tinyint NOT NULL DEFAULT '1',
      `title` varchar(255) NOT NULL DEFAULT '',
      `description` mediumtext NOT NULL,
      `image` varchar(255) NOT NULL DEFAULT '',
      `include_in_menu` tinyint NOT NULL DEFAULT '1',
      `image_width` int(10) NULL,
      `image_height` int(10) NULL,
      `featured_image` varchar(255) NOT NULL DEFAULT '',
      `featured_image_width` int(10) NULL,
      `featured_image_height` int(10) NULL,
      `product_image` varchar(255) NOT NULL DEFAULT '',
      `product_image_width` int(10) NULL,
      `product_image_height` int(10) NULL,
      `sidebar_image` varchar(255) NOT NULL DEFAULT '',
      `sidebar_image_width` int(10) NULL,
      `sidebar_image_height` int(10) NULL,
      `url_key` varchar(128) NOT NULL DEFAULT '',
      `show_products` tinyint NOT NULL DEFAULT '1',
      `is_featured` tinyint NOT NULL DEFAULT '0',
      `available_sort_by` mediumtext NOT NULL,
      `default_sort_by` varchar(128) NOT NULL DEFAULT '',
      `price_step` decimal(12,4) DEFAULT NULL,
      `page_layout` varchar(128) NOT NULL DEFAULT '',
      `layout_xml` mediumtext NOT NULL,
      `custom_design_active_from` datetime DEFAULT NULL,
      `custom_design_active_to` datetime DEFAULT NULL,
      `custom_design` varchar(128) NOT NULL DEFAULT '',
      `custom_layout_xml` mediumtext NOT NULL,
      `meta_title` varchar(255) NOT NULL DEFAULT '',
      `meta_keywords` mediumtext NOT NULL,
      `meta_description` mediumtext NOT NULL,

      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_key` (`option_page_global_id`,`store_id`),
      KEY `option_page_global_id` (`option_page_global_id`),
      KEY `option_page_store_custom_settings_id` (`option_page_store_custom_settings_id`),
      KEY `store_id` (`store_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

    ALTER TABLE `$table`
        ADD CONSTRAINT `FK_{$table}_opt_page_g` FOREIGN KEY (`option_page_global_id`)
            REFERENCES `{$this->getTable('mana_attributepage/optionPage_global')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_store` FOREIGN KEY (`store_id`)
            REFERENCES `{$this->getTable('core/store')}` (`store_id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_opt_page_s` FOREIGN KEY (`option_page_store_custom_settings_id`)
            REFERENCES `{$this->getTable('mana_attributepage/optionPage_storeCustomSettings')}` (`id`)
            ON DELETE SET NULL ON UPDATE SET NULL;
");

// seo url: additional fields for deleting URL keys using referential integrity
$table = $this->getTable('mana_seo/url');
$installer->run("
    ALTER TABLE `$table`
        ADD COLUMN `attribute_page_id` bigint(20) DEFAULT NULL,
        ADD COLUMN `option_page_id` bigint(20) DEFAULT NULL,
        ADD KEY `attribute_page_id` (`attribute_page_id`),
        ADD KEY `option_page_id` (`option_page_id`),
        ADD CONSTRAINT `FK_{$table}_attr_page_s` FOREIGN KEY (`attribute_page_id`)
            REFERENCES `{$this->getTable('mana_attributepage/attributePage_store')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `FK_{$table}_opt_page_s` FOREIGN KEY (`option_page_id`)
            REFERENCES `{$this->getTable('mana_attributepage/optionPage_store')}` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();
