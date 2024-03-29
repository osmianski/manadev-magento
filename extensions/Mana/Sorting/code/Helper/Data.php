<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Sorting module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Sorting_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_sortingMethodXmls;
    protected $_sortingMethodXmlsWithoutFilter;
    protected $_customSortingMethodCollection;
    protected $_sortingMethodsCombined;
    public $_outOfStock;
    /**
     * @return Varien_Simplexml_Element[]
     */
    public function getSortingMethodXmls($filterActive = true) {
        if (($filterActive && !$this->_sortingMethodXmls) || (!$filterActive && !$this->_sortingMethodXmlsWithoutFilter)) {
            $result = array();
            foreach ($this->coreHelper()->getSortedXmlChildren(Mage::getConfig()->getNode(), 'mana_sorting') as $code => $xml) {
                if ((!$filterActive) || ($filterActive && Mage::getStoreConfigFlag('mana_sorting/' . $code . '/enabled'))) {
                    $xml->code = $code;
                    $xml->position = (int)Mage::getStoreConfig('mana_sorting/' . $code . '/position');
                    $result[$code] = $xml;
                }
            }
            if($filterActive) {
                $this->_sortingMethodXmls = $result;
            } else {
                $this->_sortingMethodXmlsWithoutFilter = $result;
            }
        }
        return ($filterActive) ? $this->_sortingMethodXmls : $this->_sortingMethodXmlsWithoutFilter;
    }

    public function getOutOfStockOption () {
        if (!$this->_outOfStock) {
            return $this->_outOfStock = Mage::getStoreConfigFlag('mana_sorting/out_of_stock/enabled');
        }
        return $this->_outOfStock;
    }

    protected function _compareSortingMethodByPosition($a, $b) {
        $aPos = Mage::getStoreConfig('mana_sorting/' . $a . '/position');
        $bPos = Mage::getStoreConfig('mana_sorting/' . $b . '/position');

        if ($aPos - $bPos > 0) return 1;
        if ($aPos - $bPos < 0) return -1;
        return 0;
    }

    public function addManaSortingOptions($options = array()) {
        if(!isset($this->_sortingMethodsCombined)) {
            $sortMethods = array();
            foreach ($this->getSortingMethodXmls() as $xml) {
                $sortMethods[(string)$xml->code] = array(
                    'label' => (string)$xml->label,
                    'value' => (string)$xml->code,
                    'position' => (string)$xml->position,
                );
            }

            $collection = $this->getCustomSortMethodCollection();
            /** @var Mana_Sorting_Model_Method_Abstract $sortMethod */
            foreach($collection as $sortMethod) {
                $sortMethods[$sortMethod->getData('url_key')] = array(
                    'label' => $sortMethod->getData('title'),
                    'value' => $sortMethod->getData('url_key'),
                    'position' => $sortMethod->getData('position'),
                );
            }
            $this->_sortingMethodsCombined = $sortMethods;
            uksort($this->_sortingMethodsCombined, array($this, 'sortSortingMethods'));
        }
        foreach($this->_sortingMethodsCombined as $sortMethod) {
            array_push($options, array(
                    'label' => $sortMethod['label'],
                    'value' => $sortMethod['value'],
                ));
        }
        return $options;
    }

    public function sortSortingMethods($a, $b)
    {
        $ap = (int)$this->_sortingMethodsCombined[$a]['position'];
        $bp = (int)$this->_sortingMethodsCombined[$b]['position'];
        return ($ap == $bp) ? 0 : ($ap < $bp ) ? -1 : 1;
    }

    public function getManaSortingOptionLabel($sortingOptionCode) {
        foreach($this->getSortingMethodXmls() as $xml) {
            if((string)$xml->code == $sortingOptionCode) {
                return (string)$xml->label;
            }
        }
        $collection = $this->getCustomSortMethodCollection()->addUrlKeyFilter($sortingOptionCode);
        if($collection->count() > 0) {
            return $collection->getFirstItem()->getData('title');
        }
        return false;
    }

    public function isManaSortingOption($sortingOptionCode) {
        foreach($this->getSortingMethodXmls() as $xml) {
            if($sortingOptionCode == (string)$xml->code) {
                return true;
            }
        }
        $collection = $this->getCustomSortMethodCollection()->addUrlKeyFilter($sortingOptionCode);
        if($collection->count() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get catalog layer model
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory() {
        $layer = Mage::registry('current_layer');
        if ($layer) {
            return $layer->getCurrentCategory();
        }

        return Mage::getSingleton('catalog/layer')->getCurrentCategory();
    }

    public function translateUrlKey($urlKey) {
        /* @var Mage_Core_Model_Resource $res */
        $res = Mage::getSingleton('core/resource');
        $db = $res->getConnection('read');
        $select = $db->select()->from(array('store_sorting' => $res->getTableName('mana_sorting/method_store')), null)
            ->joinInner(array('global_sorting' => $res->getTableName('mana_sorting/method')),
                "`store_sorting`.`method_id` = `global_sorting`.`id`", null)
            ->where("`store_sorting`.`store_id` = ?", Mage::app()->getStore()->getId())
            ->where("`global_sorting`.`url_key` = ?", $urlKey)
            ->columns("store_sorting.url_key");
        return $db->fetchOne($select);
    }

    /**
     * @return Mana_Sorting_Resource_Method_Collection|Mana_Sorting_Resource_Method_Store_Collection
     */
    public function getCustomSortMethodCollection() {
        if (!$this->coreHelper()->inAdmin()) {
            $collection = Mage::getResourceModel('mana_sorting/method_store_collection');
            $collection->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
        }
        elseif($this->adminHelper()->isGlobal()) {
            $collection = Mage::getResourceModel('mana_sorting/method_collection');
        } else {
            $collection = Mage::getResourceModel('mana_sorting/method_store_collection');
            $collection->addFieldToFilter('store_id', $this->adminHelper()->getStore()->getId());
        }
        $collection->filterActive();
        return $collection;
    }
    public function applyOutOfStockSortingIfRequired($select) {
        $tables = $select->getPart('from');
        if (Mage::helper('mana_sorting')->getOutOfStockOption() && !array_key_exists('s', $tables)) {
            $table = Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_status');
            $select
                ->joinLeft(array('s' => $table), ' s.product_id = e.entity_id ', null)
                ->order("s.stock_status desc");
        }
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    public function adminHelper() {
        return Mage::helper('mana_admin');
    }
    #endregion
}