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
/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'catalog/product_link';
$installer->run("
	DELETE FROM `{$this->getTable($table)}` WHERE `link_type_id` = $linkTypeId;
");

$installer->endSetup();