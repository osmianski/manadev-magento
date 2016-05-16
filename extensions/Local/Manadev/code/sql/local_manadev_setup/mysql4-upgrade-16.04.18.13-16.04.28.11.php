<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE `m_license_request`
      ADD `agg_modules` TEXT NOT NULL,
      ADD `agg_frontend_urls` TEXT NOT NULL,
      ADD `agg_themes` TEXT NOT NULL
");

$table = $installer->getTable('downloadable/link_purchased_item');
$installer->run("
    ALTER TABLE `{$table}`
      ADD `agg_magento_ids` TEXT NOT NULL,
      ADD `agg_remote_ips` TEXT NOT NULL
");

$installer->endSetup();