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

$table = 'eav/attribute';
$attributeIds = $installer->getConnection()->fetchCol("
	SELECT `attribute_id` FROM `{$this->getTable($table)}` WHERE `attribute_code` IN ('m_represented_qty', 'm_represents')
");
$attributeIds = implode(',', $attributeIds);

$table = 'eav/entity_attribute';
$installer->run("
	DELETE FROM `{$this->getTable($table)}` WHERE `attribute_id` IN ($attributeIds);
");

$installer->endSetup();