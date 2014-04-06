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
class Mana_Sorting_Resource_ReviewCount extends Mage_Core_Model_Mysql4_Abstract implements Mana_Sorting_ResourceInterface
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

        $select
                ->joinLeft(
                    array(
                        'stats' => new Zend_Db_Expr("(SELECT r.entity_pk_value AS product_id, count(*) review_count" .
                                " FROM {$this->getTable('review/review')} AS r" .

                                // " WHERE (r.created_at BETWEEN '{$from}' AND '{$to}') AND" .
                                " WHERE " .
                                $db->quoteInto(" r.status_id = ?", Mage_Review_Model_Review::STATUS_APPROVED) .
                                " GROUP BY r.entity_pk_value)")
                    ),
                    "stats.product_id = e.entity_id",
                    null
                );
        $direction = $direction == 'asc' ? 'desc' : 'asc';
        $select->order("stats.review_count {$direction}");
    }

    public function getDate()
    {
        return Mage::app()->getLocale()->date();
    }
}