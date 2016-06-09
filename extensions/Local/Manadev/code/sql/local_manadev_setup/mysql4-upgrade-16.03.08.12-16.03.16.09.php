<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;
$installer->startSetup();
$table = "downloadable_link_purchased_item";
$installer->run("
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_store_info` TEXT NOT NULL DEFAULT '');
");

$installer->run("
    CREATE TABLE `m_domain_history` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `item_id` int(10) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `m_registered_domain` varchar(255) NOT NULL DEFAULT '',
      `m_store_info` text NOT NULL,
      PRIMARY KEY (`id`)
    )  ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';

");
$installer->endSetup();