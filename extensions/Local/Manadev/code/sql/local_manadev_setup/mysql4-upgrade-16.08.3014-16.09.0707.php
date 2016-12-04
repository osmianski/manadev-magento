<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();
$table = "downloadable_link_purchased_item";
$installer->run("
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN `m_registered_domain_pending` VARCHAR(255) NULL AFTER `m_registered_domain`;
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN `m_pending_hash` VARCHAR(255) NULL AFTER `m_registered_domain_pending`;
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN `m_store_info_pending` TEXT NULL AFTER `m_store_info`;
    
");
$installer->endSetup();