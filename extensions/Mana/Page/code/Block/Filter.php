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
abstract class Mana_Page_Block_Filter extends Mage_Core_Block_Template {
    /**
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_productCollection;
    protected $_attributes;
    protected $_usedAttributes = array();

    public function getTodayDate() {
        return Mage::app()->getLocale()->date()->toString(Varien_Date::DATE_INTERNAL_FORMAT);
    }

    public function getDate()
    {
        return Mage::app()->getLocale()->date();
    }

    protected function _prepareLayout() {
        Mage::helper('mana_core/layout')->delayPrepareLayout($this, 100);

        return $this;
    }

    public function delayedPrepareLayout() {
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
        $this
            ->_prepareProductCollection()
            ->prepareSortableFieldsByCategory($layer->getCurrentCategory());

        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        return parent::_prepareLayout();
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
    protected function _prepareProductCollection() {
        return $this;
    }
    public function getStoreId() {
        return Mage::app()->getStore()->getId();
    }

    public function joinAttribute($attributeCode) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $core->collectionFind($this->getAttributes(), 'attribute_code', $attributeCode);

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $this->_productCollection->getConnection();
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        $alias = 'mp_'.$attributeCode;
        $this->_productCollection->getSelect()->joinLeft(
            array($alias => $attribute->getBackendTable()),
            implode(' AND ', array(
                "`$alias`.`entity_id` = `e`.`entity_id`",
                $db->quoteInto("`$alias`.`attribute_id` = ?", $attribute->getId()),
                "`$alias`.`store_id` = 0",
            )),
            null
        );

        return "`$alias`.`value`";
    }

    public function joinField($field) {
        $alias = 'mp_' . $field;
        /* @var $resource Mage_Catalog_Model_Resource_Product */
        $resource = $this->_productCollection->getResource();

        $this->_productCollection->getSelect()->joinLeft(
            array($alias => $resource->getTable('catalog/product')),
            "`$alias`.`entity_id` = `e`.`entity_id`",
            null
        );

        return "`$alias`.`$field`";
    }

    public function getAttributes()
    {
        if (is_null($this->_attributes)) {
            $this->_attributes  = Mage::getSingleton('eav/config')
                ->getEntityType('catalog_product')
                ->getAttributeCollection();

            if ($this->_usedAttributes) {
                $this->_attributes->addFieldToFilter('attribute_code', array('in' => $this->_usedAttributes));
            }
        }

        return $this->_attributes;
    }

}