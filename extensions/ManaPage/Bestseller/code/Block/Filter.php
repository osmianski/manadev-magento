<?php
/**
 * @category    Mana
 * @package     ManaPage_New
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPage_Bestseller_Block_Filter extends Mana_Page_Block_Filter {
    public function prepareProductCollection() {
        $to = $this->getDate()->addDay(1)->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        $from = $this->getDate()->addYear(-1)->toString(Varien_Date::DATE_INTERNAL_FORMAT);

        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');
        $db = $res->getConnection('read');

        $select = $this->_productCollection->getSelect();
        $columns = $select->getPart(Varien_Db_Select::COLUMNS);
        foreach ($columns as $index => $column) {
            if (isset($column[2]) && $column[2] == 'cat_index_position') {
                unset($columns[$index]);
            }
        }
        $select->setPart(Varien_Db_Select::COLUMNS, $columns);
        $select
            ->joinLeft(array('stats' => new Zend_Db_Expr("(SELECT stats.product_id AS product_id, SUM(stats.qty_ordered - IFNULL(stats.qty_canceled, 0)) AS qty_ordered".
                " FROM {$res->getTableName('sales/order_item')} AS stats".
                " INNER JOIN {$res->getTableName('sales/order')} AS o ON".
                " (o.entity_id = stats.order_id AND".
                " (o.created_at BETWEEN '{$from}' AND '{$to}'))  AND o.state NOT IN ('pending_payment', 'new', 'canceled') AND".
                $db->quoteInto(" o.store_id = ?", Mage::app()->getStore()->getId()).
                " GROUP BY stats.product_id)")),
                "stats.product_id = e.entity_id", array('cat_index_position' => new Zend_Db_Expr('-stats.qty_ordered')));

        $this->_condition = 'stats.qty_ordered > 0';

        return $this;
    }

}