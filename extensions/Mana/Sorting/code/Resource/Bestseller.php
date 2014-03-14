<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Resource_Bestseller extends Mage_Core_Model_Mysql4_Abstract implements Mana_Sorting_ResourceInterface {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('catalog');
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param string $order
     * @param string $direction
     */
    public function setOrder($collection, $order, $direction) {
        $to = $this->getDate()->addDay(1)->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        $from = $this->getDate()->addYear(-1)->toString(Varien_Date::DATE_INTERNAL_FORMAT);

        $select = $collection->getSelect();
        $db = $this->getReadConnection();

        $select
            ->joinLeft(array('stats' => new Zend_Db_Expr("(SELECT stats.product_id AS product_id, SUM(stats.qty_ordered - IFNULL(stats.qty_canceled, 0)) AS qty_ordered".
                " FROM {$this->getTable('sales/order_item')} AS stats".
                " INNER JOIN {$this->getTable('sales/order')} AS o ON".
                " (o.entity_id = stats.order_id AND".
                " (o.created_at BETWEEN '{$from}' AND '{$to}'))  AND o.state NOT IN ('pending_payment', 'new', 'canceled') AND".
                $db->quoteInto(" o.store_id = ?", Mage::app()->getStore()->getId()).
                " GROUP BY stats.product_id)")),
                "stats.product_id = e.entity_id", null);
        $direction = $direction == 'asc' ? 'desc' : 'asc';
        $select->order("stats.qty_ordered {$direction}");
    }

    public function getDate() {
        return Mage::app()->getLocale()->date();
    }
}