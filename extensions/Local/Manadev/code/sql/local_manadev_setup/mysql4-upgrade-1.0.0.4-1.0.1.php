<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
foreach (array('lastname', 'city', 'country_id', 'postcode', 'street', 'telephone') as $attributeCode) {
	$installer->run("
		UPDATE `{$this->getTable('eav_attribute')}` SET is_required = 0 
		WHERE (`entity_type_id` = 2) AND (`attribute_code` = '$attributeCode');
	");
	$attributeId = $installer->getConnection()->fetchOne("
		SELECT `attribute_id` FROM `{$this->getTable('eav_attribute')}`
		WHERE (`entity_type_id` = 2) AND (`attribute_code` = '$attributeCode');
	");
	$installer->run("
		UPDATE `{$this->getTable('customer_eav_attribute')}` SET `validate_rules` = NULL 
		WHERE (`attribute_id` = {$attributeId});
	");
}

// INSERT HERE: actual installation steps

$installer->endSetup();