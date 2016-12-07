<?php

class Local_Manadev_Helper_Products extends Mage_Core_Helper_Abstract
{
    public function getMagento2ProductCollection() {
        return $this->_getPlatformProductCollection(2);
    }

    public function getMagento1ProductCollection() {
        return $this->_getPlatformProductCollection(1);
    }

    public function getUpsellProductCollection() {
        $product = Mage::registry('product');
        /* @var $product Mage_Catalog_Model_Product */
        $collection = $product->getUpSellProductCollection()
            ->setPositionOrder()
            ->addStoreFilter()
        ;
        if (Mage::helper('catalog')->isModuleEnabled('Mage_Checkout')) {
            Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($collection,
                Mage::getSingleton('checkout/session')->getQuoteId()
            );

            $this->_addProductAttributesAndPrices($collection);
        }
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        $collection->load();
        return $collection;
    }

    protected function _getPlatformProductCollection($platform) {
        $collection = $this->_getProductCollection();

        $res = Mage::getSingleton('core/resource');
        $db = $res->getConnection('read');

        $platformAttributeId = $db->fetchOne($db->select()
            ->from(array('a' => 'eav_attribute'), 'attribute_id')
            ->joinInner(array('t' => 'eav_entity_type'), $db->quoteInto(
                "`t`.`entity_type_id` = `a`.`entity_type_id` AND `t`.`entity_type_code` = ?", 'catalog_product'), null)
            ->where("`a`.`attribute_code` = ?", 'platform')
        );

        $collection->getSelect()
            ->joinInner(array('platform' => 'catalog_product_entity_int'),
                "`platform`.`entity_id` = `e`.`entity_id` AND ".
                $db->quoteInto("`platform`.`store_id` = ?", 0) . " AND " .
                $db->quoteInto("`platform`.`value` = ?", $platform) . " AND " .
                $db->quoteInto("`platform`.`attribute_id` = ?", $platformAttributeId), null);

        return $collection;
    }

    protected function _getProductCollection()
    {
        $todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');

        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());


        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->addAttributeToFilter('news_from_date', array('or'=> array(
                0 => array('date' => true, 'to' => $todayEndOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter('news_to_date', array('or'=> array(
                0 => array('date' => true, 'from' => $todayStartOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter(
                array(
                    array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                    array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
                    )
              )
            ->addAttributeToSort('news_from_date', 'desc')
        ;

        return $collection;
    }

    protected function _addProductAttributesAndPrices(Mage_Catalog_Model_Resource_Product_Collection $collection) {
        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addUrlRewrite();
    }
}