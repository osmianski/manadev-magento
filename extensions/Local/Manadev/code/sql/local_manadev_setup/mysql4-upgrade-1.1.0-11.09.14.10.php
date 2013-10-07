<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('customer_address', 'm_company_code', array(
    'input'         => 'text', 
    'type'          => 'varchar',
    'label'         => 'Company Code',
	'sort_order' 	=> 31,
	'required'      => false,
));
$installer->addAttribute('customer_address', 'm_vat_number', array(
    'input'         => 'text', 
    'type'          => 'varchar',
    'label'         => 'VAT Number',
	'sort_order' 	=> 32,
	'required'      => false,
));

foreach (array('sales_flat_quote_address', 'sales_flat_order_address') as $table) {
	$installer->run("
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN ( 
			`m_company_code` varchar(255) NOT NULL default ''
		);
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN ( 
			`m_vat_number` varchar(255) NOT NULL default ''
		);
	");
}

foreach (array('sales_flat_invoice', 'sales_flat_creditmemo') as $table) {
	$installer->run("
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_exchange_rate` decimal(16, 4) NULL);
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_total` decimal(16, 4) NULL);
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_vat` decimal(16, 4) NULL);
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_grand_total` decimal(16, 4) NULL);
	");
}
foreach (array('sales_flat_invoice', 'sales_flat_creditmemo', 'sales_flat_invoice_grid', 'sales_flat_creditmemo_grid') as $table) {
	$installer->run("
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_usd_total` decimal(16, 4) NULL);
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_usd_vat` decimal(16, 4) NULL);
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_date` datetime NULL);
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_timezone` varchar(80) NULL);
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_vat_percent` decimal(16, 4) NULL);
	");
}

$installer->endSetup();