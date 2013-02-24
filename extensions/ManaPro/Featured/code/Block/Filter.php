<?php
/**
 * @category    Mana
 * @package     ManaPro_Featured
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Featured_Block_Filter extends Mage_Core_Block_Template/*Mage_Catalog_Block_Product_List*/ {
    protected function _beforeToHtml() {
        $layer = $this->getLayer();
        if ($this->getShowRootCategory()) {
            $this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
        }

        if (Mage::registry('product')) {
            $categories = Mage::registry('product')->getCategoryCollection()
                    ->setPage(1, 1)
                    ->load();
            if ($categories->count()) {
                $this->setCategoryId(current($categories->getIterator()));
            }
        }

        $origCategory = null;
        if ($this->getCategoryId()) {
            $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
            if ($category->getId()) {
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
            }
        }

        $this->_productCollection = $layer->getProductCollection();
        $dateToday = date('m/d/y');
        $tomorrow = mktime(0, 0, 0, date('m'), date('d') + 1, date('y'));
        $dateTomorrow = date('m/d/y', $tomorrow);
        $this->_productCollection
                ->addAttributeToFilter('m_featured_from_date', array('date' => true, 'to' => $dateToday))
                ->addAttributeToFilter('m_featured_to_date', array('or' => array(
            array('date' => true, 'from' => $dateTomorrow),
            array('is' => new Zend_Db_Expr('null')))
        ), 'left');

        $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());
        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        return parent::_beforeToHtml();
    }
    protected function _addProductAttributesAndPrices($collection) {
        return $collection
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addUrlRewrite();
    }
    public function getLayer() {
        $layer = Mage::registry('current_layer');
        if ($layer) {
            return $layer;
        }
        return Mage::getSingleton('catalog/layer');
    }
    public function prepareSortableFieldsByCategory($category) {
        if (!$this->getAvailableOrders()) {
            $this->setAvailableOrders($category->getAvailableSortByOptions());
        }
        $availableOrders = $this->getAvailableOrders();
        if (!$this->getSortBy()) {
            if ($categorySortBy = $category->getDefaultSortBy()) {
                if (!$availableOrders) {
                    $availableOrders = $this->_getConfig()->getAttributeUsedForSortByArray();
                }
                if (isset($availableOrders[$categorySortBy])) {
                    $this->setSortBy($categorySortBy);
                }
            }
        }

        return $this;
    }
}