<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterAttributes
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

if (defined('COMPILER_INCLUDE_PATH')) {
    throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $this Mage_Core_Model_Resource_Setup */
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
if (method_exists($this->getConnection(), 'allowDdlCache')) {
    $this->getConnection()->allowDdlCache();
}
$connection = $installer->getConnection();

$installer->startSetup();

$attributeCode = Mage::getResourceModel('manapro_filterattributes/stockAvailability')
    ->getAttributeCode();

$attributeOptions = array(
    1 => 'In Stock',
    2 => 'Available for backorder');

$installer->addAttribute('catalog_product', $attributeCode, array(
    'group'         => 'General',
    'input'         => 'select',
	'source'        => 'eav/entity_attribute_source_table',
    'type'          => 'int',
    'backend'       => '',
    'label'         => 'Stock availability',
    'user_defined'  => true,
	'required'      => false,
    'is_configurable'  => 0,
    'searchable'    => false,
    'filterable'    => false,
    'filterable_in_search' => false,
    'comparable'    => true,
    'html_allowed_on_front' => true,
    'visible_in_advanced_search' => false,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
));

$attributeId = $installer->getAttributeId('catalog_product', $attributeCode);
$model = Mage::getModel('eav/entity_attribute')->load($attributeId);

// add options
$tableOptions = $installer->getTable('eav_attribute_option');
$tableOptionValues = $installer->getTable('eav_attribute_option_value');

foreach ($attributeOptions as $sortOrder => $label) {
    $installer->getConnection()->insert($tableOptions, array(
        'attribute_id' => $attributeId,
        'sort_order' => $sortOrder,
    ));

    $optionId = (int)$installer->getConnection()->lastInsertId($tableOptions, 'option_id');

    $installer->getConnection()->insert($tableOptionValues, array(
        'option_id' => $optionId,
        'store_id' => 0,
        'value' => $label,
    ));
}

$model
     ->setDefaultValue($model->getSource()->getOptionId('In Stock'))
     ->save();

Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_attribute')
    ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);

if (method_exists($installer->getConnection(), 'disallowDdlCache')) {
    $installer->getConnection()->disallowDdlCache();
}
$installer->endSetup();
