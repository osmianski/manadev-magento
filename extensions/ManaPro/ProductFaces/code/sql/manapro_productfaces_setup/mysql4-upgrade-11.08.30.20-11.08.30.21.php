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

$linkTypeId = Mage::getResourceModel('manapro_productfaces/collection')->getRepresentingLinkTypeId();
$installer->run("
	INSERT  INTO {$installer->getTable('catalog/product_link_attribute')} 
	(`link_type_id`,`product_link_attribute_code`,`data_type`) 
	VALUES 
	($linkTypeId,'position','int')
");

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'cataloginventory/stock_item';
$installer->run("
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN ( 
		`m_represented_qty` decimal(12,4) NOT NULL default '0.0000'
	);

");

$installer->endSetup();