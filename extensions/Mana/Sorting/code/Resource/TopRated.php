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
class Mana_Sorting_Resource_TopRated extends Mage_Core_Model_Mysql4_Abstract implements Mana_Sorting_ResourceInterface
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

        if (isset($tables['top_rated_stats'])) {
            return;
        }

        $select
                ->joinLeft(
                    array(
                        'top_rated_stats' => new Zend_Db_Expr("(SELECT r.entity_pk_value AS product_id, avg(o.value) average_rating" .
                                " FROM {$this->getTable('review/review')} AS r" .
                                " INNER JOIN {$this->getTable('review/review_store')} AS rs ON" .
                                " (r.review_id = rs.review_id)" .
                                " INNER JOIN {$this->getTable('rating/rating_option_vote')} AS v ON" .
                                " (r.review_id = v.review_id)" .
                                " INNER JOIN {$this->getTable('rating/rating_option')} AS o ON" .
                                " (v.option_id = o.option_id)" .
                                " WHERE " .
                                $db->quoteInto(" r.status_id = ?", Mage_Review_Model_Review::STATUS_APPROVED) .
                                " AND rs.store_id = " . Mage::app()->getStore()->getId() .
                                " GROUP BY r.entity_pk_value)")
                    ),
                    "top_rated_stats.product_id = e.entity_id",
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
        $direction = $direction == 'asc' ? 'desc' : 'asc';
        $select->order("top_rated_stats.average_rating {$direction}");
        $sqlString = $select->__toString();
    }

    public function getDate()
    {
        return Mage::app()->getLocale()->date();
    }
}