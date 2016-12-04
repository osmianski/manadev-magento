<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;
$installer->startSetup();
$table = "downloadable_link_purchased_item";
$installer->run("
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_registered_domain` VARCHAR(255) NOT NULL DEFAULT '');
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_support_valid_til` TIMESTAMP);
");
$installer->endSetup();