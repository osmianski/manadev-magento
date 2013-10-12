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

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
if (method_exists($this->getConnection(), 'allowDdlCache')) {
    $this->getConnection()->allowDdlCache();
}

$installer->startSetup();
$stockStatusAttributeCode = Mage::getResourceModel('manapro_filterattributes/stockstatus')->getstockStatusAttributeCode();

$installer->addAttribute('catalog_product', $stockStatusAttributeCode, array(
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
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'option'        => array('values' => array(1=>'In Stock', 2=>'Out of Stock'))
));

$model = Mage::getModel('eav/entity_attribute')
     ->load($installer->getAttributeId('catalog_product', $stockStatusAttributeCode));
$model
     ->setDefaultValue($model->getSource()->getOptionId('In Stock'))
     ->save();

$installer->addAttributeOption($options);
// add attribute to all attributesets
$attributeId= $installer->getAttributeId('catalog_product', $stockStatusAttributeCode);
$model=Mage::getModel('eav/entity_setup','core_setup');
$allAttributeSetIds=$model->getAllAttributeSetIds('catalog_product');
foreach ($allAttributeSetIds as $attributeSetId) {
    try{
        $attributeGroupId=$model->getAttributeGroup('catalog_product',$attributeSetId,'General');
    }
    catch(Exception $e) {
        $attributeGroupId=$model->getDefaultArrtibuteGroupId('catalog/product',$attributeSetId);
    }
    $model->addAttributeToSet('catalog_product',$attributeSetId,$attributeGroupId, $attributeId);
}

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();
