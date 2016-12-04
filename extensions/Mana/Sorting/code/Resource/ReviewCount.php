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
        $tables = $select->getPart('from');

        if (isset($tables['review_count_stats'])) {
            return;
        }

        $select
            ->joinLeft(
                 array('review_count_stats' => $this->getTable('review/review_aggregate')),
                 'review_count_stats.entity_pk_value = e.entity_id AND review_count_stats.store_id=' . Mage::app()->getStore()->getId(),
                 array()
            );
        Mage::helper('mana_sorting')->applyOutOfStockSortingIfRequired($select);
        $direction = $direction == 'asc' ? 'desc' : 'asc';
        $select->order("review_count_stats.reviews_count {$direction}");
    }

    public function getDate()
    {
        return Mage::app()->getLocale()->date();
    }
}