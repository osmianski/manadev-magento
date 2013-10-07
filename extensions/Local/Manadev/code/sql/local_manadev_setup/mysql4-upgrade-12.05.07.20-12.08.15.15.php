<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

foreach (array('tax_class') as $table) {
	$installer->run("
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN ( 
			`auto_assign_condition` text NOT NULL
		);
	");
}

$installer->endSetup();