<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;
$installer->startSetup();
$table = "downloadable_link_purchased_item";
$installer->run("
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_license_verification_no` VARCHAR(50));
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_license_no` VARCHAR(50));
");
$installer->endSetup();