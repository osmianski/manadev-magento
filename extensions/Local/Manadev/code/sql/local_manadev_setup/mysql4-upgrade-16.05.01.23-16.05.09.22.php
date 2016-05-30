<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getTable('local_manadev/license_request');
$installer->run(
    "
    ALTER TABLE `{$table}`
      ADD `last_checked` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
"
);

$installer->endSetup();