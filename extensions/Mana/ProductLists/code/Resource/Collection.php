<?php
/**
 * @category    Mana
 * @package     Mana_ProductLists
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
abstract class Mana_ProductLists_Resource_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection {
	public function getLinkTypeId() {
		if (!$this->_linkTypeId) {
			$this->_linkTypeId = $this->getConnection()->fetchOne(
				"SELECT `link_type_id` FROM {$this->getTable('catalog/product_link_type')} 
				WHERE `code` = ?", $this->_getLinkType());
		}
		return $this->_linkTypeId;
	}
	abstract protected function _getLinkType();
    public function applyProductFilter($value, $products)
    {
    	if ($value) {
	        if (!empty($products)) {
	            if (!is_array($products)) {
	                $products = array($products);
	            }
	            $this->_hasLinkFilter = true;
	            $this->getSelect()->where('e.entity_id IN (?)', $products);
	        }
	        else {
	            $this->_hasLinkFilter = true;
	            $this->getSelect()->where('e.entity_id IN (0)', $products);
	        }
    	}
    	else {
    		if (!empty($products)) {
	            if (!is_array($products)) {
	                $products = array($products);
	            }
	            $this->_hasLinkFilter = true;
	            $this->getSelect()->where('e.entity_id NOT IN (?)', $products);
	        }
    	}
        return $this;
    }
	
}