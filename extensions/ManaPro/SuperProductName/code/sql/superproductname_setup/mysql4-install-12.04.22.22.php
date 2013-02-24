<?php
/**
 * @category    Mana
 * @package     ManaPro_SuperProductName
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$attrCode = 'name_wysiwyg';
$attrGroupName = 'General';
$attrLabel = 'Name WYSIWYG';
$attrNote = 'Name WYSIWYG';

$attrIdTest = $installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, $attrCode);

if (!empty($attrIdTest)) {
    $installer->removeAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode);
}

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode, array(
    'group' => $attrGroupName,
    'sort_order' => 7,
    'type' => 'text',
    'backend' => '',
    'frontend' => '',
    'label' => $attrLabel,
    'note' => $attrNote,
    'input' => 'textarea',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => '',
    'visible_on_front' => false,
    'unique' => false,
    'is_configurable' => false,
    'used_for_promo_rules' => false,
    'is_html_allowed_on_front' => true,
    'wysiwyg_enabled' => true,
    'searchable' => true
));

$installer->endSetup();
