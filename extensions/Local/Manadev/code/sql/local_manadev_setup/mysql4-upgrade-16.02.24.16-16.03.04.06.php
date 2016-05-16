<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'platform', array(
    'type' => 'int',
    'label' => 'Platform',
    'input' => 'select',
    'source' => 'local_manadev/platform',
    'required' => false,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'is_user_defined' => 1,
));
$installer->endSetup();