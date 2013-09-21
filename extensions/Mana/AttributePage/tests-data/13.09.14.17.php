<?php
/**
 * @author Mana Team
 */

/* @var $this Mana_Core_Test_Setup */

/* @var $utils Mana_Core_Helper_Utils */
$utils = Mage::helper('mana_core/utils');

/* @var $dbHelper Mana_Db_Helper_Data */
$dbHelper = Mage::helper('mana_db');

/* @var $colorAttribute Mage_Catalog_Model_Resource_Eav_Attribute */
$colorAttribute = $dbHelper->getModel('catalog/resource_eav_attribute');
$colorAttribute->load('color', 'attribute_code');

/* @var $attributePage Mana_AttributePage_Model_Page */
$attributePage = $dbHelper->getModel('mana_attributepage/page/global');
$attributePage->overrideData('attribute_id_0', $colorAttribute->getId());
$attributePage->save();

$utils->reindex('mana_db');
