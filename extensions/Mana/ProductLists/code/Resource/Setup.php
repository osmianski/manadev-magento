<?php
/**
 * @category    Mana
 * @package     Mana_ProductLists
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract helper functions for setup scripts whch add new product link types 
 * @author Mana Team
 *
 */
class Mana_ProductLists_Resource_Setup extends Mana_Core_Resource_Eav_Setup {
	public function installProductLinks($linkTypes = null) {
		if (is_null($linkTypes)) {
            $linkTypes = $this->getDefaultProductLinks();
        }
        foreach ($linkTypes as $linkTypeCode => $linkTypeDefinition) {
        	$this->addProductLinkType($linkTypeCode, $linkTypeDefinition);
        	foreach ($linkTypeDefinition['attributes'] as $attributeCode => $attributeDefinition) {
        		$this->addProductLinkAttribute($linkTypeCode, $attributeCode, $attributeDefinition);
        	}
        }
        return $this;
	}
	public function getDefaultProductLinks() {
		return array();
	}
	
	public function addProductLinkType($code, $definition) {
		if (!$this->getProductLinkTypeId($code)) {
			$this->run("INSERT INTO {$this->getTable('catalog_product_link_type')} (`code`) VALUES ('$code')");
		}
	}
	public function removeProductLinkType($code) {
		$this->run("DELETE FROM {$this->getTable('catalog_product_link_type')} WHERE `code` = '$code'");
	}
	public function addProductLinkAttribute($linkTypeCode, $code, $definition) {
		$row = $this->_conn->fetchRow(
			"SELECT `product_link_attribute_id`,`data_type` FROM {$this->getTable('catalog/product_link_attribute')} 
			WHERE (`link_type_id` = {$this->getProductLinkTypeId($linkTypeCode)}) AND (`product_link_attribute_code` = '$code')");
		if (!empty($row) && !empty($row['product_link_attribute_id']) && !empty($row['data_type']) &&
			$row['data_type'] != $definition['backend_type']) 
		{
			$this->run("DELETE FROM {$this->getTable('catalog/product_link_attribute')} 
				WHERE (`product_link_attribute_id` = {$row['product_link_attribute_id']})");
		}
		$this->run("
			INSERT INTO {$this->getTable('catalog/product_link_attribute')} 
			(`link_type_id`,`product_link_attribute_code`,`data_type`) 
			VALUES ({$this->getProductLinkTypeId($linkTypeCode)}, '$code', '{$definition['backend_type']}')
		");
	}
	public function removeProductLinkAttribute($linkTypeCode, $code) {
		$this->run("DELETE FROM {$this->getTable('catalog/product_link_attribute')} 
			WHERE (`link_type_id` = {$this->getProductLinkTypeId($linkTypeCode)}) AND (`product_link_attribute_code` = '$code')");
	}
	protected $_productLinkTypeIds = array();
	public function getProductLinkTypeId($code) {
		if (empty($this->_productLinkTypeIds[$code])) {
			$this->_productLinkTypeIds[$code] = $this->_conn->fetchOne(
				"SELECT `link_type_id` FROM {$this->getTable('catalog_product_link_type')} WHERE `code` = '$code'");
		}
		return $this->_productLinkTypeIds[$code];
	}
}