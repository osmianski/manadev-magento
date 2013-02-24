<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("INSERT INTO {$installer->getTable('catalog_product_link_type')} (`code`) 
	VALUES ('".ManaPro_ProductFaces_Resource_Collection::_TYPE."')");

$installer->endSetup();