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

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'cataloginventory/stock_item';
$installer->run("
		ALTER TABLE `{$this->getTable($table)}` ADD COLUMN ( 
		`m_represents` tinyint NOT NULL default '0'
	);

");

$installer->endSetup();