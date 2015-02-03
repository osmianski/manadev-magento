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
class Mana_Sorting_Resource_Quantity extends Mage_Core_Model_Mysql4_Abstract implements Mana_Sorting_ResourceInterface
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
        $select = $collection->getSelect();

        $tables = $select->getPart('from');
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
        if($this->coreHelper()->isManadevManySKUInstalled()) {
            $select->order("e.m_represented_qty {$direction}");
        } else {
            $tables = $select->getPart('from');
            if (!array_key_exists('s', $tables)) {
                $select
                    ->joinLeft(
                        array('s' => $this->getTable('cataloginventory/stock_item')),
                        ' s.product_id = e.entity_id ',
                        array()
                    );
            }
            $select->order("s.qty {$direction}");
        }
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }
    #endregion
}