<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Mana_Content_Block_Book_Product_List extends Mage_Catalog_Block_Product_Abstract
{
    protected $_productCollection;

    public function _construct() {
        $this->setTemplate('mana/content/book/product-list.phtml');
    }

    public function getProductCollection() {
        if (!$this->_productCollection) {
            $res = Mage::getSingleton('core/resource');
            $db = $res->getConnection('read');

            /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection = Mage::getResourceModel('catalog/product_collection');

            $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

            $collection = $this->_addProductAttributesAndPrices($collection)
                ->addStoreFilter()
                ->addAttributeToSort('user_guide_position', 'asc');

            if ($this->getData('platform')) {
                $platformAttributeId = $db->fetchOne($db->select()
                    ->from(array('a' => 'eav_attribute'), 'attribute_id')
                    ->joinInner(array('t' => 'eav_entity_type'), $db->quoteInto(
                        "`t`.`entity_type_id` = `a`.`entity_type_id` AND `t`.`entity_type_code` = ?", 'catalog_product'), null)
                    ->where("`a`.`attribute_code` = ?", 'platform')
                );

                $collection->getSelect()->joinInner(array('platform' => 'catalog_product_entity_int'),
                    "`platform`.`entity_id` = `e`.`entity_id` AND " .
                    $db->quoteInto("`platform`.`store_id` = ?", 0) . " AND " .
                    $db->quoteInto("`platform`.`value` = ?", $this->getData('platform')) . " AND " .
                    $db->quoteInto("`platform`.`attribute_id` = ?", $platformAttributeId),
                    null
                );

                $collection->getSelect()->where("`e`.`user_guide_url` <> ''");
            }
            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }

    public function getContentPageUrl($product) {
        return Mage::getUrl() . $product->getUserGuideUrl();
    }
}