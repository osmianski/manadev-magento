<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$attributeId = $installer->getConnection()->fetchOne("
	SELECT `attribute_id` FROM `{$this->getTable('eav_attribute')}`
	WHERE (`entity_type_id` = 1) AND (`attribute_code` = 'lastname');
");
$installer->run("
	UPDATE `{$this->getTable('customer_eav_attribute')}` SET `validate_rules` = NULL 
	WHERE (`attribute_id` = {$attributeId});
");

// INSERT HERE: actual installation steps

$installer->endSetup();