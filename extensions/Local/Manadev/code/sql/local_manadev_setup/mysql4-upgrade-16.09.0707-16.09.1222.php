<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();
$table = "m_domain_history";
$installer->run("
    ALTER TABLE `{$this->getTable($table)}` CHANGE `m_registered_domain` `m_registered_domain` VARCHAR(255) NULL DEFAULT '';
    ALTER TABLE `{$this->getTable($table)}` CHANGE `m_store_info` `m_store_info` TEXT NULL DEFAULT '';
");
$installer->endSetup();