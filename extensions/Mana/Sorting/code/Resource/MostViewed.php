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
class Mana_Sorting_Resource_MostViewed extends Mage_Core_Model_Mysql4_Abstract implements Mana_Sorting_ResourceInterface
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
        $productViewEvent = 0;
        foreach (Mage::getModel('reports/event_type')->getCollection() as $eventType) {
            if ($eventType->getEventName() == 'catalog_product_view') {
                $productViewEvent = $eventType->getId();
                break;
            }
        }

        $to = $this->getDate()->addDay(1)->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        $from = $this->getDate()->addYear(-1)->toString(Varien_Date::DATE_INTERNAL_FORMAT);

        $select = $collection->getSelect();
        $db = $this->getReadConnection();

        $select
                ->joinLeft(
                    array(
                        'stats' => new Zend_Db_Expr("(SELECT stats.object_id AS product_id, count(*) AS view_count" .
                                " FROM {$this->getTable('reports/event')} AS stats" .
                                " WHERE (stats.logged_at BETWEEN '{$from}' AND '{$to}') AND" .
                                $db->quoteInto(" stats.event_type_id = ?", $productViewEvent) .
                                " GROUP BY stats.object_id)")
                    ),
                    "stats.product_id = e.entity_id",
                    null
                );
        $direction = $direction == 'asc' ? 'asc' : 'desc';
        $select->order("stats.view_count {$direction}");
    }

    public function getDate()
    {
        return Mage::app()->getLocale()->date();
    }
}