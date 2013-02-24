<?php
/**
 * @category    Mana
 * @package     ManaPage_Bestseller
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPage_Bestseller_Block_Widget extends Mana_Page_Block_Widget
{
    protected function _prepareCollection($collection)
    {
        $to = $this->getDate()->addDay(1)->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        $from = $this->getDate()->addYear(-1)->toString(Varien_Date::DATE_INTERNAL_FORMAT);

        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');
        $db = $res->getConnection('read');

        $select = $collection->getSelect();
        $columns = $select->getPart(Varien_Db_Select::COLUMNS);
        foreach ($columns as $index => $column) {
            if (isset($column[2]) && $column[2] == 'cat_index_position') {
                unset($columns[$index]);
            }
        }
        $select->setPart(Varien_Db_Select::COLUMNS, $columns);
        $select
            ->join(array('stats' => new Zend_Db_Expr("(SELECT stats.product_id AS product_id, SUM(stats.qty_ordered) AS qty_ordered".
                " FROM {$res->getTableName('sales/order_item')} AS stats".
                " INNER JOIN {$res->getTableName('sales/order')} AS o ON".
                " (o.entity_id = stats.order_id AND".
                " (o.created_at BETWEEN '{$from}' AND '{$to}')) AND".
                $db->quoteInto(" o.store_id = ?", Mage::app()->getStore()->getId()).
                " GROUP BY stats.product_id)")),
                "stats.product_id = e.entity_id", array('cat_index_position' => new Zend_Db_Expr('-stats.qty_ordered')));

        return $this;
    }

    public function getType() {
        return 'bestseller';
    }
}