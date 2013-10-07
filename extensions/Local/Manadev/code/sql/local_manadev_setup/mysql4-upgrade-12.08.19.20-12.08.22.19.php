<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();

foreach (array('sales_flat_order_item', 'sales_flat_invoice_item', 'sales_flat_creditmemo_item') as $table) {
    $installer->run("ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_preserve_grand_total` smallint NOT NULL default '0');");
    $installer->run("ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_base_price` decimal(12,4) NOT NULL default '0');");
}
foreach (array('sales_flat_order', 'sales_flat_invoice', 'sales_flat_creditmemo') as $table) {
    $installer->run("ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_recalculated` tinyint NOT NULL default '0');");
}
foreach (array('sales_flat_invoice', 'sales_flat_creditmemo', 'sales_flat_invoice_grid', 'sales_flat_creditmemo_grid') as $table) {
    $installer->run("
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_sales_account` varchar(10) NOT NULL DEFAULT '');
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_country` varchar(80) NOT NULL DEFAULT '');
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_is_business` varchar(80) NOT NULL DEFAULT '');
	");
}
$installer->endSetup();