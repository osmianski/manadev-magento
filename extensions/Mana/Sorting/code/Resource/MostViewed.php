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
        $tables = $select->getPart('from');
        if (isset($tables['most_viewed_stats'])) {
            return;
        }

        $select
                ->joinLeft(
                    array(
                        'most_viewed_stats' => new Zend_Db_Expr("(SELECT most_viewed_stats.object_id AS product_id, count(*) AS view_count" .
                                " FROM {$this->getTable('reports/event')} AS most_viewed_stats" .
                                " WHERE (most_viewed_stats.logged_at BETWEEN '{$from}' AND '{$to}') AND" .
                                $db->quoteInto(" most_viewed_stats.event_type_id = ?", $productViewEvent) .
                                " GROUP BY most_viewed_stats.object_id)")
                    ),
                    "most_viewed_stats.product_id = e.entity_id",
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
        $select->order("most_viewed_stats.view_count {$direction}");
    }

    public function getDate()
    {
        return Mage::app()->getLocale()->date();
    }
}