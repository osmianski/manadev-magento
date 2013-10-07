<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'is_service', array(
    'type' => 'int',
    'label' => 'Is Service',
    'input' => 'select',
    'source' => 'eav/entity_attribute_source_boolean',
    'required' => false,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));
$installer->endSetup();