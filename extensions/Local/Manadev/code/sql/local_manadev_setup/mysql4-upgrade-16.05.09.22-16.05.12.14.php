<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();
$table = "downloadable_link_purchased_item";
$installer->run("
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_key_public` TEXT);
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_key_private` TEXT);
");
$installer->endSetup();