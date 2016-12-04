<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();
$table = "m_license_request";
$installer->run("
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN `changed_data` VARCHAR(255) NULL;
");
$installer->endSetup();