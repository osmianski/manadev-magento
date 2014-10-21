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
$installer->run(
    "
	INSERT  INTO {$installer->getTable('catalog/product_link_attribute')}
	(`link_type_id`,`product_link_attribute_code`,`data_type`)
	VALUES
	($linkTypeId,'m_selling_qty','int')
"
);
Mage::register('m_product_faces_reindex_flat', true, true);

$installer->endSetup();