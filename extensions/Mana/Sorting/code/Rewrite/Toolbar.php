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
class Mana_Sorting_Rewrite_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar {
    protected function _construct() {
        parent::_construct();
        $this->_addOrders();
   }

    public function setAvailableOrders($orders) {
        $this->_availableOrder = $orders;
        $this->_addOrders();
        return $this;
    }

    public function setCollection($collection) {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            if (!$this->_setOrder($this->_collection, $this->getCurrentOrder(), $this->getCurrentDirection())) {
                $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
            }
        }

        return $this;
    }

    protected function _addOrders() {
        foreach ($this->sortingHelper()->getSortingMethodXmls() as $xml) {
            $this->_availableOrder[(string)$xml->code] = (string)$xml->label;
        }
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param string $order
     * @param string $direction
     * @return bool
     */
    protected function _setOrder($collection, $order, $direction) {
        $xmls = $this->sortingHelper()->getSortingMethodXmls();
        if (isset($xmls[$order])) {
            /* @var $resource Mana_Sorting_ResourceInterface */
            $resource = Mage::getResourceSingleton((string)$xmls[$order]->resource);
            $resource->setOrder($collection, $order, $direction);
            return true;
        }
        else {
            return false;
        }
    }

    #region Dependencies
    /**
     * @return Mana_Sorting_Helper_Data
     */
    public function sortingHelper() {
        return Mage::helper('mana_sorting');
    }

    #endregion
}