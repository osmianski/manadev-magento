<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$installer->run("
	UPDATE `{$this->getTable('eav_attribute')}` SET is_required = 0 
	WHERE (`entity_type_id` = 1) AND (`attribute_code` = 'lastname');
");

// INSERT HERE: actual installation steps

$installer->endSetup();