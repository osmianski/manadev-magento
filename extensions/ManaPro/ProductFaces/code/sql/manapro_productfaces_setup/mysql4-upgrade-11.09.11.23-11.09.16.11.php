<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer ManaPro_ProductFaces_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = 'catalog/product';
$installer->run("
	ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_represented_qty` decimal(12,4) NOT NULL default '0.0000');
	ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_represents` tinyint NOT NULL default '0');
");

$installer->addAttribute('catalog_product', 'm_represented_qty', array(
    'input'         => 'label', 
    'type'          => 'static',
    'label'         => 'Represented Qty',
    'backend'       => '',
    'visible'       => 1,
	'required'      => 0,
    'user_defined' => 0,
    'searchable' => 0,
    'filterable' => 0,
    'comparable'    => 0,
    'visible_on_front' => 0,
    'visible_in_advanced_search'  => 0,
    'is_html_allowed_on_front' => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
$installer->addAttribute('catalog_product', 'm_represents', array(
    'type'          => 'static',
    'label'         => 'Represents',
    'backend'       => '',
    'visible'       => 1,
	'input'         => 'label',
	'required'      => 0,
    'user_defined' => 0,
    'searchable' => 0,
    'filterable' => 0,
    'comparable'    => 0,
    'visible_on_front' => 0,
    'visible_in_advanced_search'  => 0,
    'is_html_allowed_on_front' => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));


$installer->endSetup();