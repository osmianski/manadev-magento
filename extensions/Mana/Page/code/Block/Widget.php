<?php
/**
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Page_Block_Widget extends Mage_Catalog_Block_Product_Abstract
    implements Mage_Widget_Block_Interface
{
    protected function _construct()
    {
        parent::_construct();

        $this->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');
    }

    public function getTodayDate() {
        return Mage::app()->getLocale()->date()->toString(Varien_Date::DATE_INTERNAL_FORMAT);
    }

    public function getDate()
    {
        return Mage::app()->getLocale()->date();
    }


    /**
     * Retrieve how much products should be displayed.
     *
     * @return int
     */
    public function getProductsCount()
    {
        if (!$this->hasData('products_count')) {
            return parent::getProductsCount();
        }

        return $this->_getData('products_count');
    }

    protected function _beforeToHtml()
    {
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->setPageSize($this->getProductsCount())
            ->setCurPage(1);
        $this->_prepareCollection($collection);
        $this->setProductCollection($collection);

        if ($this->getTemplateCms()) {
            $this->setTemplate($this->getTemplateCms());
        }
//        if (!$this->getTemplateFile() && ($pos = strrpos($this->getTemplate(), '/')) !== false) {
//            $this->setTemplate('mana/page/'.substr($this->getTemplate(), $pos + 1));
//        }

        return parent::_beforeToHtml();
    }

    public function getStoreId() {
        return Mage::app()->getStore()->getId();
    }

    abstract protected function _prepareCollection($collection);
    abstract public function getType();

}