<?php
/**
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Page module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Page_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getTodayDate() {
        return Mage::app()->getLocale()->date()->toString(Varien_Date::DATE_INTERNAL_FORMAT);
    }

    public function getDate() {
        return Mage::app()->getLocale()->date();
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param string $attributeCode
     * @param $attributes
     * @return string
     */
    public function joinAttribute($collection, $attributeCode, $attributes) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $core->collectionFind($attributes, 'attribute_code', $attributeCode);

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $collection->getConnection();
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        $alias = 'mp_' . $attributeCode;
        $collection->getSelect()->joinLeft(
            array($alias => $attribute->getBackendTable()),
            implode(' AND ', array(
                "`$alias`.`entity_id` = `e`.`entity_id`",
                $db->quoteInto("`$alias`.`attribute_id` = ?", $attribute->getId()),
                "`$alias`.`store_id` = 0",
            )), null);

        return "`$alias`.`value`";
    }

    /**
     * @param string[] $usedAttributes
     * @return mixed
     */
    public function getAttributes($usedAttributes) {
        $attributes = Mage::getSingleton('eav/config')
            ->getEntityType('catalog_product')
            ->getAttributeCollection();

        if ($usedAttributes) {
            $attributes->addFieldToFilter('attribute_code', array('in' => $usedAttributes));
        }

        return $attributes;
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param string $field
     * @return string
     */
    public function joinField($collection, $field) {
        $alias = 'mp_' . $field;
        /* @var $resource Mage_Catalog_Model_Resource_Product */
        $resource = $collection->getResource();

        $collection->getSelect()->joinLeft(
            array($alias => $resource->getTable('catalog/product')),
            "`$alias`.`entity_id` = `e`.`entity_id`",
            null
        );

        return "`$alias`.`$field`";
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function addProductAttributesAndPrices($collection) {
        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addUrlRewrite();
    }

    /**
     * @param int $maxProductCount
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function createProductCollection($startingFromProduct, $maxProductCount) {
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

        $collection = $this->addProductAttributesAndPrices($collection)
            ->addStoreFilter();

        if ($maxProductCount) {
            $collection->getSelect()->limit($maxProductCount, $startingFromProduct);
        }
        else {
            $collection->getSelect()->limit(PHP_INT_MAX, $startingFromProduct);
        }

        return $collection;

    }
}