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
class Mana_Sorting_Resource_Newest extends Mage_Core_Model_Mysql4_Abstract implements Mana_Sorting_ResourceInterface {
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

        $select = $collection->getSelect();

        Mage::helper('mana_sorting')->applyOutOfStockSortingIfRequired($select);
        $direction = $direction == 'asc' ? 'desc' : 'asc';
        $select->order("e.created_at {$direction}");
    }
}