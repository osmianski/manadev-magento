<?php
/** 
 * @category    Mana
 * @package     Mana_License
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

if (defined('COMPILER_INCLUDE_PATH')) {
    throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
if (method_exists($this->getConnection(), 'allowDdlCache')) {
    $this->getConnection()->allowDdlCache();
}

$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_category');
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

if (!$installer->getAttributeId($entityTypeId, 'm_show_in_layered_navigation')) {
    $installer->addAttribute('catalog_category', 'm_show_in_layered_navigation', array(
        'type' => 'int',
        'label' => 'Show In Layered Navigation Filter',
        'input' => 'select',
        'source'   => 'eav/entity_attribute_source_boolean',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'required' => false,
        'default'  => 1,
    ));

    $installer->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        'm_show_in_layered_navigation',
        '11'
    );

    $attributeId = $installer->getAttributeId($entityTypeId, 'm_show_in_layered_navigation');

    $installer->run("
        INSERT INTO `{$installer->getTable('catalog_category_entity_int')}`
        (`entity_type_id`, `attribute_id`, `entity_id`, `value`)
            SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, '1'
                FROM `{$installer->getTable('catalog_category_entity')}`;
    ");

    Mage::helper('mana_core/db')->scheduleReindexing('catalog_category_flat');
}


if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();
