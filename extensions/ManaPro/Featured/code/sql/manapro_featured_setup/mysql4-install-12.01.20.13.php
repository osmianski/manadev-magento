<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$this->startSetup();
$this->cleanCache();

foreach ($this->getAllAttributeSetIds('catalog_product') as $setId) {
    $this->addAttributeGroup('catalog_product', $setId, 'Featured on Category Pages', 3);
}

$this->addAttribute('catalog_product', 'm_featured_from_date', array(
    'group' => 'Featured on Category Pages',
    'label' => 'Featured From Date',
    'type' => 'datetime',
    'input' => 'date',
    'class' => 'validate-date',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'used_in_product_listing' => true,
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => '',
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
));

$this->addAttribute('catalog_product', 'm_featured_to_date', array(
    'group' => 'Featured on Category Pages',
    'label' => 'Featured To Date',
    'type' => 'datetime',
    'input' => 'date',
    'class' => 'validate-date',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'used_in_product_listing' => true,
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => '',
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
));

$this->addAttribute('catalog_product', 'm_featured_description', array(
    'group' => 'Featured on Category Pages',
    'label' => 'Featured Description',
    'note' => 'Leave empty to use Short Description',
    'type' => 'text',
    'input' => 'textarea',
    'class' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'used_in_product_listing' => true,
    'wysiwyg_enabled' => true,
    'is_html_allowed_on_front' => true,
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => '',
    'searchable' => true,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
));

$this->endSetup();
