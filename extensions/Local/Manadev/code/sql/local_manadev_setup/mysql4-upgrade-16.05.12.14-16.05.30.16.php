<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();
$table = "downloadable_link_purchased_item";
$installer->run("
    ALTER TABLE `{$this->getTable($table)}` CHANGE `purchased_id` `purchased_id` INT(10) UNSIGNED NULL DEFAULT '0' COMMENT 'Purchased ID';
");
$installer->endSetup();