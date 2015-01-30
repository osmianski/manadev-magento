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
                 array('stats' => $this->getTable('review/review_aggregate')),
                 'stats.entity_pk_value = e.entity_id AND stats.store_id=' . Mage::app()->getStore()->getId(),
                 array()
            );
        if (Mage::helper('mana_sorting')->getOutOfStockOption()) {
            $select
                    ->joinLeft(
                        array('s' => $this->getTable('cataloginventory/stock_item')),
                        ' s.product_id = e.entity_id ',
                        array()
                    );
            $select->order("s.is_in_stock desc");
        }
        $direction = $direction == 'asc' ? 'asc' : 'desc';
        $select->order("stats.reviews_count {$direction}");
    }

    public function getDate()
    {
        return Mage::app()->getLocale()->date();
    }
}