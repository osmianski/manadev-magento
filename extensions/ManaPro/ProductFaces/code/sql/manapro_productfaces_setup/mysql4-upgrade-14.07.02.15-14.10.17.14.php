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

$linkTypeId = Mage::getResourceModel('manapro_productfaces/collection')->getRepresentingLinkTypeId();

$column = 'm_pack_qty';
$data_type = 'int';

$installer->run(
    "
	INSERT  INTO {$installer->getTable('catalog/product_link_attribute')}
	(`link_type_id`,`product_link_attribute_code`,`data_type`)
	VALUES
	($linkTypeId,'{$column}','{$data_type}')
"
);

$installer->run(
    "
    INSERT INTO {$installer->getTable('catalog/product_link_attribute')}_{$data_type}(product_link_attribute_id, link_id, value)
    SELECT cpla.product_link_attribute_id, cpl.link_id, 1 AS `value` FROM {$installer->getTable('catalog/product_link')} cpl
      INNER JOIN {$installer->getTable('catalog/product_link_type')} cplt ON cplt.link_type_id = cpl.link_type_id
      INNER JOIN {$installer->getTable('catalog/product_link_attribute')} cpla ON cpla.link_type_id = cplt.link_type_id
    WHERE cplt.code = '".ManaPro_ProductFaces_Resource_Collection::_TYPE."'
      AND cpla.product_link_attribute_code = '{$column}'
    "
);
Mage::register('m_product_faces_reindex_flat', true, true);

$installer->endSetup();