<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'sales_flat_invoice_grid';
$installer->run("
	ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_exchange_rate` decimal(16, 4) NULL);
	ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_total` decimal(16, 4) NULL);
	ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_vat` decimal(16, 4) NULL);
	ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_grand_total` decimal(16, 4) NULL);
");

$table = 'sales_flat_creditmemo_grid';
$installer->run("
	ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_exchange_rate` decimal(16, 4) NULL);
	ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_total` decimal(16, 4) NULL);
	ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_vat` decimal(16, 4) NULL);
	ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_grand_total` decimal(16, 4) NULL);
");

$installer->endSetup();