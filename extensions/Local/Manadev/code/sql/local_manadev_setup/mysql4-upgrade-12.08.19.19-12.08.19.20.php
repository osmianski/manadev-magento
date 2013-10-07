<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = 'customer/customer_group';
$installer->run("
  ALTER TABLE `{$this->getTable($table)}` ADD COLUMN ( 
      `tax_independent_code` varchar(80) NOT NULL default ''
  );
");

$installer->endSetup();