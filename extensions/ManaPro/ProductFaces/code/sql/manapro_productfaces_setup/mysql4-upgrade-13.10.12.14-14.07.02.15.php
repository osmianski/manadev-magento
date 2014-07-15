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

$installer->addAttribute('catalog_product', 'm_represented_qty_text', array(
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'Represented Qty (Text)',
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

Mage::register('m_product_faces_reindex_flat', true, true);

$installer->endSetup();