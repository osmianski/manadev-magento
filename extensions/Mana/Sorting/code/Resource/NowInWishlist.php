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
class Mana_Sorting_Resource_NowInWishlist extends Mage_Core_Model_Mysql4_Abstract implements Mana_Sorting_ResourceInterface
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_setResource('catalog');
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param string $order
     * @param string $direction
     */
    public function setOrder($collection, $order, $direction)
    {
        $to = $this->getDate()->addDay(1)->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        $from = $this->getDate()->addYear(-1)->toString(Varien_Date::DATE_INTERNAL_FORMAT);

        $select = $collection->getSelect();
        $db = $this->getReadConnection();
        $tables = $select->getPart('from');
        if (isset($tables['now_in_wishlist_stats'])) {
            return;
        }

        $select
                ->joinLeft(
                    array(
                        'now_in_wishlist_stats' => new Zend_Db_Expr("(SELECT now_in_wishlist_stats.product_id, count(*) AS wishlist_count" .
                                " FROM {$this-> getTable('wishlist/item')} AS now_in_wishlist_stats" .
                                " WHERE (now_in_wishlist_stats.added_at BETWEEN '{$from}' AND '{$to}') " .
                                " GROUP BY now_in_wishlist_stats.product_id)")
                    ),
                    "now_in_wishlist_stats.product_id = e.entity_id",
                    null
                );
        if (Mage::helper('mana_sorting')->getOutOfStockOption() && !array_key_exists('s', $tables)) {
            $select
                    ->joinLeft(
                        array('s' => $this->getTable('cataloginventory/stock_item')),
                        ' s.product_id = e.entity_id ',
                        array()
                    );
            $select->order("s.is_in_stock desc");
        }
        $direction = $direction == 'asc' ? 'asc' : 'desc';
        $select->order("now_in_wishlist_stats.wishlist_count {$direction}");
    }

    public function getDate()
    {
        return Mage::app()->getLocale()->date();
    }
}