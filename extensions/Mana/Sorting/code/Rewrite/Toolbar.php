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
    protected $overrideDefaultOrder;

    public function setAvailableOrders($orders) {
        $category = $this->sortingHelper()->getCategory();
        if($category->getAvailableSortBy()) {
            foreach ($category->getAvailableSortBy() as $sortBy) {
                if ($this->sortingHelper()->isManaSortingOption($sortBy)) {
                    $orders[$sortBy] = $this->sortingHelper()->getManaSortingOptionLabel($sortBy);
                }
            }
        }
        parent::setAvailableOrders($orders);
        if(!$category->getAvailableSortBy()) {
            $this->_addOrders();
        }
    }

    public function setDefaultOrder($field) {
        if(isset($this->overrideDefaultOrder)) {
            $field = $this->overrideDefaultOrder;
        } else {
            $category = $this->sortingHelper()->getCategory();
            if($category->getData('default_sort_by')) {
                $field = $category->getData('default_sort_by');
            } else {
                $field = Mage::getSingleton('catalog/config')->getProductListDefaultSortBy();
            }
        }
        return parent::setDefaultOrder($field);
    }

    public function overrideDefaultOrder($field) {
        $this->overrideDefaultOrder = $field;
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
        $sortingMethods = $this->sortingHelper()->addManaSortingOptions();
        foreach($sortingMethods as $sortMethod) {
            $this->_availableOrder[$sortMethod['value']] = $sortMethod['label'];
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
        $customMethods = $this->sortingHelper()->getCustomSortMethodCollection()->addFieldToFilter('url_key', $order);
        if (isset($xmls[$order])) {
            /* @var $resource Mana_Sorting_ResourceInterface */
            $resource = Mage::getResourceSingleton((string)$xmls[$order]->resource);
            if (!($resource instanceof Mana_Sorting_ResourceInterface)) {
                throw new Exception('Sorting resource class must implement Mana_Sorting_ResourceInterface.');
            }
            $resource->setOrder($collection, $order, $direction);
            return true;
        }
        elseif(count($customMethods)) {
            $id = $customMethods->getFirstItem()->getId();
            /** @var Mana_Sorting_Resource_CustomSortMethod $resource */
            $resource = Mage::getResourceSingleton('mana_sorting/customSortMethod');
            $resource->setCustomSortMethodId($id);
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