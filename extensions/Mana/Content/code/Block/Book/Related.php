<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Block_Book_Related extends Mage_Catalog_Block_Product_List_Related {
    protected function _construct() {
        $this->setTemplate('mana/content/book/related.phtml');
    }

    protected function _prepareData() {
        $bookPage = Mage::registry('current_book_page');

        $this->_itemCollection = Mage::getModel('catalog/product')->getCollection()
                ->joinTable(array('mprp' => 'mana_content/page_relatedProduct'), 'product_id=entity_id', array('product_id'), "{{table}}.`page_global_id` = " . $bookPage->getData('page_global_id'))
                ->addAttributeToSort('position', 'asc')
                ->addStoreFilter();

        Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($this->_itemCollection,
            Mage::getSingleton('checkout/session')->getQuoteId()
        );
        $this->_addProductAttributesAndPrices($this->_itemCollection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->_itemCollection);

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

}