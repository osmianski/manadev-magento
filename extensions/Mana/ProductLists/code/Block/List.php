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
abstract class Mana_ProductLists_Block_List extends Mage_Catalog_Block_Product_Abstract {
    protected $_collection;
    protected $_items;
    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }
    abstract protected function _getLinkType();
    abstract protected function _getCollectionType();
    protected function _createCollection() {
    	return Mage::helper('mana_productlists')->createCollection($this->_getCollectionType(), $this->_getProduct(), 'frontend_data');
    }
    protected function _beforeToHtml()
    {
        /* @var $product Mage_Catalog_Model_Product */ $product = $this->_getProduct();
        $this->_collection = $this->_createCollection();
        if (Mage::getStoreConfigFlag('mana_productlists/'.$this->_getLinkType().'/exclude_cart_products')) {
	        Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($this->_collection,
	            Mage::getSingleton('checkout/session')->getQuoteId()
	        );
        }
        $this->_addProductAttributesAndPrices($this->_collection);

        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->_collection);

        if (($itemLimit = Mage::getStoreConfig('mana_productlists/'.$this->_getLinkType().'/item_limit')) && $itemLimit > 1) {
            $this->_collection->setPageSize($itemLimit - 1);
        }

        $this->_collection->load();
		$this->_items = array_merge(array(clone $this->_getProduct()), $this->_collection->getItems());
        
        foreach ($this->_items as $product) {
            $product->setDoNotUseCategoryId(true);
        }
    	return parent::_beforeToHtml();
    }
    /**
     * @return Mana_ProductLists_Resource_Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }
    public function getItems()
    {
        return $this->_items;
    }
}