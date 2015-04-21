<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Updates individual links
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Resource_Link extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link {
    public function updateProductLink($productId, $linkedProductId, $linkInfo, $typeId) {
    	$attributes = $this->getAttributesByType($typeId);
        
    	$linkId = $this->_getReadAdapter()->fetchOne("
        	SELECT `link_id` FROM {$this->getMainTable()}
        	WHERE (`product_id` = ?) AND (`linked_product_id` = ?) AND (`link_type_id` = ?)
        ", array($productId, $linkedProductId, $typeId));
        
    	foreach ($attributes as $attributeInfo) {
        	$attributeTable = $this->getAttributeTypeTable($attributeInfo['type']);
            if ($attributeTable && isset($linkInfo[$attributeInfo['code']])) {
		    	$valueId = $this->_getReadAdapter()->fetchOne("
		        	SELECT `value_id` FROM {$attributeTable}
		        	WHERE (`product_link_attribute_id` = ?) AND (`link_id` = ?)
		        ", array($attributeInfo['id'], $linkId));
            	
		    	if (!$valueId) {
	            	$this->_getWriteAdapter()->insert($attributeTable, array(
	                	'product_link_attribute_id' => $attributeInfo['id'],
	                    'link_id'                   => $linkId,
	                    'value'                     => $linkInfo[$attributeInfo['code']]
	                ));
		    	}
		    	else {
		    		$this->_getWriteAdapter()->update($attributeTable, array(
	                    'value'                     => $linkInfo[$attributeInfo['code']]
		    		), 'value_id = '.$valueId);
		    	}
            }
        }
        return $this;
    }

    public function getRepresentedProductId($productId) {
    	$linkTypeId = Mage::getResourceModel('manapro_productfaces/collection')->getRepresentingLinkTypeId();
    	return $this->_getReadAdapter()->fetchOne("
    		SELECT `product_id` FROM {$this->getMainTable()}
    		WHERE (`linked_product_id` = $productId) AND (`link_type_id` = $linkTypeId)
    	");
    }
    
    public function getRepresentingProductsAndOptions($productId) {
    	if (!$productId) return array();
    	
    	$linkTypeId = Mage::getResourceModel('manapro_productfaces/collection')->getRepresentingLinkTypeId();
    	$tables = $this->_getReadAdapter()->fetchAssoc("
    		SELECT product_link_attribute_code, product_link_attribute_id, data_type
    		FROM {$this->getMainTable()}_attribute
    		WHERE `link_type_id` = $linkTypeId
    	");
    	$sql = "
    		SELECT l.`linked_product_id`, p.value AS m_parts, u.value AS m_unit, e.value AS position, COALESCE(s.value, 1) AS m_pack_qty
    		FROM {$this->getMainTable()} AS l
    		LEFT JOIN {$this->getMainTable()}_attribute_{$tables['m_parts']['data_type']} AS p 
    			ON (p.product_link_attribute_id = {$tables['m_parts']['product_link_attribute_id']}) AND (p.link_id = l.link_id) 
    		LEFT JOIN {$this->getMainTable()}_attribute_{$tables['m_unit']['data_type']} AS u 
    			ON (u.product_link_attribute_id = {$tables['m_unit']['product_link_attribute_id']}) AND (u.link_id = l.link_id) 
    		LEFT JOIN {$this->getMainTable()}_attribute_{$tables['position']['data_type']} AS e 
    			ON (e.product_link_attribute_id = {$tables['position']['product_link_attribute_id']}) AND (e.link_id = l.link_id)
    	    LEFT JOIN {$this->getMainTable()}_attribute_{$tables['m_pack_qty']['data_type']} AS s
    			ON (s.product_link_attribute_id = {$tables['m_pack_qty']['product_link_attribute_id']}) AND (s.link_id = l.link_id)
    		WHERE (l.`product_id` = $productId) AND (`link_type_id` = $linkTypeId)
    	";
    	return $this->_getReadAdapter()->fetchAll($sql);
    }
    
    public function getAllRepresentedProductIds() {
    	$linkTypeId = Mage::getResourceModel('manapro_productfaces/collection')->getRepresentingLinkTypeId();
    	return $this->_getReadAdapter()->fetchCol("
    		SELECT DISTINCT `product_id` FROM {$this->getMainTable()}
    		WHERE (`link_type_id` = $linkTypeId)
    	");
    }

    public function isRepresentedProduct($productId) {
    	if ($productId) {
	    	$linkTypeId = Mage::getResourceModel('manapro_productfaces/collection')->getRepresentingLinkTypeId();
	    	return $this->_getReadAdapter()->fetchOne("
	    		SELECT 1 FROM {$this->getMainTable()}
	    		WHERE (`link_type_id` = $linkTypeId) AND (`product_id` = $productId)
	    	");
    	}
    	else {
    		return false;
    	}
    }

    public function getAllRepresentingProductIds() {
        $linkTypeId = Mage::getResourceModel('manapro_productfaces/collection')->getRepresentingLinkTypeId();
        return $this->_getReadAdapter()->fetchCol("
    		SELECT DISTINCT `linked_product_id` FROM {$this->getMainTable()}
    		WHERE (`link_type_id` = $linkTypeId)
    	");
    }
}