<?php

class Local_Manadev_Block_Product_Widget_New extends Mage_Catalog_Block_Product_Widget_New {
    protected function _construct() {
        parent::_construct();

        $this->unsetData('cache_lifetime');
    }

    protected function _getProductCollection() {
        $collection = parent::_getProductCollection();

        if ($this->hasData('platform')) {
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
                    $db->quoteInto("`platform`.`value` = ?", $this->getData('platform')) . " AND " .
                    $db->quoteInto("`platform`.`attribute_id` = ?", $platformAttributeId), null);

        }

        return $collection;
    }
}