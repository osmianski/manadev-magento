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

$installer->startSetup();
$stockStatusAttributeCode = Mage::getResourceModel('manapro_filterattributes/stockStatus')->getstockStatusAttributeCode();
$attributeOptions = array(
    1 => 'In Stock',
    2 => 'Out of Stock');

$installer->addAttribute('catalog_product', $stockStatusAttributeCode, array(
    'group'         => 'General',
    'input'         => 'select',
	'source'        => 'eav/entity_attribute_source_table',
    'type'          => 'int',
    'backend'       => '',
    'label'         => 'In stock?',
    'user_defined'  => true,
	'required'      => false,
    'configurable'  => false,
    'searchable'    => false,
    'filterable'    => true,
    'filterable_in_search' => true,
    'comparable'    => true,
    'html_allowed_on_front' => true,
    'visible_in_advanced_search' => false,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
));

$model = Mage::getModel('eav/entity_attribute')
     ->load($installer->getAttributeId('catalog_product', $stockStatusAttributeCode));

// add options
$tableOptions = $installer->getTable('eav_attribute_option');
$tableOptionValues = $installer->getTable('eav_attribute_option_value');
$attributeId = $installer->getAttributeId('catalog_product', $stockStatusAttributeCode);

foreach ($attributeOptions as $sortOrder => $label) {
    $data = array(
        'attribute_id' => $attributeId,
        'sort_order' => $sortOrder,
    );
    $installer->getConnection()->insert($tableOptions, $data);
    $optionId = (int)$installer->getConnection()->lastInsertId($tableOptions, 'option_id');
    $data = array(
        'option_id' => $optionId,
        'store_id' => 0,
        'value' => $label,
    );
    $installer->getConnection()->insert($tableOptionValues, $data);
}

$model
     ->setDefaultValue($model->getSource()->getOptionId('In Stock'))
     ->save();

/*$installer->addAttributeOption($options);*/

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();
