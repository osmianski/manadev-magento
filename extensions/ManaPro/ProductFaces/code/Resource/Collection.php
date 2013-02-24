<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Retrieves collection of representing products for given product
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Resource_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection {
	const _TYPE = 'm_represents';
	
	protected static $_representingLinkTypeId;
	public function getRepresentingLinkTypeId() {
		if (!self::$_representingLinkTypeId) {
			self::$_representingLinkTypeId = $this->getConnection()->fetchOne(
				"SELECT `link_type_id` FROM {$this->getTable('catalog/product_link_type')} 
				WHERE `code` = ?", self::_TYPE);
		}
		return self::$_representingLinkTypeId;
	}
    public function setIsStrongMode($value = true)
    {
        $this->_isStrongMode = $value;
        return $this;
    }
	protected function _afterLoad() {
		foreach ($this->_items as $item) {
			if (!$item->getMParts()) {
				$item->setMParts(1);
			}
		}
	}
	public function addLinkedProductFilter($product) {
		if ($product instanceof Mage_Catalog_Model_Product) {
			$product = $product->getId();
		}
		if ($product) {
			$this->_hasLinkFilter = true;
			$this->getSelect()->where('links.linked_product_id = ?', $product);
		}
		else {
			$this->_hasLinkFilter = true;
			$this->getSelect()->where('links.linked_product_id = 0');
		}
		return $this;
	}
	protected $_retrieveRepresentedProduct;
	public function retrieveRepresentedProduct($value = true) {
		$this->_retrieveRepresentedProduct = $value;
		return $this;
	}
    protected function _joinLinks()
    {
    	if ($this->_retrieveRepresentedProduct) {
	        $joinCondition = 'links.product_id = e.entity_id AND links.link_type_id = ' . $this->_linkTypeId;
	        $joinType = 'join';
	        if ($this->getProduct() && $this->getProduct()->getId()) {
	            $this->getSelect()->where('e.entity_id = -1');
	        }
	        elseif ($this->_isStrongMode) {
	            $this->getSelect()->where('e.entity_id = -1');
	        }
	        if($this->_hasLinkFilter) {
	            $this->getSelect()->$joinType(
	                array('links' => $this->getTable('catalog/product_link')),
	                $joinCondition,
	                array('link_id')
	            );
	            $this->joinAttributes();
	        }
    	}
    	else {
	        $joinCondition = 'links.linked_product_id = e.entity_id AND links.link_type_id = ' . $this->_linkTypeId;
	        $joinType = 'join';
	        if ($this->getProduct() && $this->getProduct()->getId()) {
	            if ($this->_isStrongMode) {
	                $this->getSelect()->where('links.product_id = ?', $this->getProduct()->getId());
	            }
	            else {
	                $joinType = 'joinLeft';
	                $joinCondition.= ' AND links.product_id = ' . $this->getProduct()->getId();
	            }
	        }
	        elseif ($this->_isStrongMode) {
	            $this->getSelect()->where('e.entity_id = -1');
	        }
	        if($this->_hasLinkFilter) {
	            $this->getSelect()->$joinType(
	                array('links' => $this->getTable('catalog/product_link')),
	                $joinCondition,
	                array('link_id')
	            );
	            $this->joinAttributes();
	        }
	    }
        return $this;
    }
}