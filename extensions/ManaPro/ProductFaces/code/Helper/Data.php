<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for ManaPro_ProductFaces module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_ProductFaces_Helper_Data extends Mage_Core_Helper_Abstract {
	/**
	 * Returns collection of products representing (and sharing inventry of) given product
	 * @param Mage_Catalog_Model_Product $product
	 * @return ManaPro_ProductFaces_Resource_Collection
	 */
	public function getRepresentingProductCollection($product) {
		/* @var $collection ManaPro_ProductFaces_Resource_Collection */ $collection = Mage::getResourceModel('manapro_productfaces/collection');
        /* @var $linkModel Mage_Catalog_Model_Product_Link */ $linkModel = $product->getLinkInstance();
        
		$linkModel->setLinkTypeId($collection->getRepresentingLinkTypeId());
        $collection
        	->setLinkModel($linkModel)
        	->setIsStrongMode()
        	->setProduct($product);
        	
	    for ($i = 0; $i < 10; $i++) {
	    	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/quick_edit/attribute'.$i, $product->getStoreId())) {
	        	$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
	        	$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
	        	foreach ($attributes as $attribute) {
					switch ($attribute->getFrontend()->getInputType()) {
				    	case 'price': 
				        case 'text': 
				        case 'select':
				        	$collection->addAttributeToSelect($attributeCode);
				        	break;
				    }
        		}
        	}
        }
        	
        return $collection;
	}

    /**
     * Retrieve array of representing products
	 * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getRepresentingProducts($product)
    {
        if (!$product->hasMRepresentingProducts()) {
            $products = array();
            $collection = $this->getRepresentingProductCollection($product);
            foreach ($collection as $item) {
                $products[] = $item;
            }
            $product->setMRepresentingProducts($products);
        }
        return $product->getMRepresentingProducts();
    }

    /**
     * Retrieve representing products identifiers
	 * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getRepresentingProductIds($product)
    {
        if (!$product->hasMRepresentingProductIds()) {
            $ids = array();
            foreach ($this->getRepresentingProducts($product) as $item) {
                $ids[] = $item->getId();
            }
            $product->setMRepresentingProductIds($ids);
        }
        return $product->getMRepresentingProductIds();
    }

	public function getRepresentedProduct($product) {
		/* @var $collection ManaPro_ProductFaces_Resource_Collection */ $collection = Mage::getResourceModel('manapro_productfaces/collection');
        /* @var $linkModel Mage_Catalog_Model_Product_Link */ $linkModel = $product->getLinkInstance();
        
		$linkModel->setLinkTypeId($collection->getRepresentingLinkTypeId());
        $collection
        	->setLinkModel($linkModel)
        	->addAttributeToSelect('*')
        	->retrieveRepresentedProduct()
        	->addLinkedProductFilter($product)
        	->getSelect()->distinct(true);
        
        $result = null;
		if ($product instanceof Mage_Catalog_Model_Product) {
			$product = $product->getId();
		}
        foreach ($collection as $item) {
        	if (!$result) {
        		//if ($item->getId() != $product) {
        			$result = $item;
        		//}
        	}
        	else {
        		throw new Mage_Core_Exception($this->__('Product %s represents more than one product (see % and %s as examples). It is not allowed.', 
        			$product, $result->getId(), $item->getId()));
        	}
        }
        return $result;
        
	}
    public function decodeGridSerializedInput($encoded)
    {
        $result = array();
        parse_str($encoded, $decoded);
        foreach($decoded as $key => $value) {
			$result[$key] = null;
            parse_str(base64_decode($value), $result[$key]);
        }
        return $result;
    }
	protected $_attributesForQuickEdit;
    public function getAttributesForQuickEdit($storeId) {
    	if (!$this->_attributesForQuickEdit) {
			$this->_attributesForQuickEdit = array();
			for ($i = 0; $i < 10; $i++) {
	        	if ($attributeCode = Mage::getStoreConfig('manapro_productfaces/quick_edit/attribute'.$i, $storeId)) {
	        		$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
	        		$attributes->getSelect()->where('attribute_code = ?', $attributeCode);
	        		foreach ($attributes as $attribute) {
				        switch ($attribute->getFrontend()->getInputType()) {
				        	case 'price': 
				        	case 'text': 
				        	case 'select':
				        		$this->_attributesForQuickEdit[] = $attributeCode;
				        		break;
				        }
	        		}
	        	}
			}
    	}
		return $this->_attributesForQuickEdit;
	}

	public function logQtyChanges($message) {
        if (!Mage::getStoreConfigFlag('manapro_productfaces/developer/log_qty_changes')) {
            return;
        }

        try {
            throw new Exception();
        }
        catch (Exception $e) {
            $message = "\n\n$message\n\n{$e->getTraceAsString()}";
        }
        Mage::log($message, Zend_Log::DEBUG, 'm_qty_changes.log');
	}
}