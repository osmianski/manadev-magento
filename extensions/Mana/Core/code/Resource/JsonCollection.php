<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getScopeName()
 */
class Mana_Core_Resource_JsonCollection extends Varien_Data_Collection {
    protected $_rawData = array();
    protected $_data = array();
    protected $_mFilters = array();

    /**
     * @param $data
     * @return Mana_Core_Resource_JsonCollection
     */
    public function setData($data) {
        foreach (array_keys($data) as $index) {
            if (!isset($data[$index]['id'])) {
                $data[$index]['id'] = $index;
            }
        }
        $this->_data = $data;
        $this->_rawData = $data;

        return $this;
    }

    public function getData() {
        return $this->_data;
    }
    public function load($printQuery = false, $logQuery = false) {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->_beforeLoad();

        $this->_renderFilters()
            ->_renderOrders()
            ->_renderLimit();

        $data = $this->getData();

        if (is_array($data)) {
            foreach ($data as $row) {
                $item = $this->getNewEmptyItem();
                $item->addData($row);
                $this->addItem($item);
            }
        }

        $this->_setIsLoaded();
        $this->_afterLoad();

        return $this;
    }

    protected function _beforeLoad() {
        return $this;
    }

    protected function _afterLoad() {
        return $this;
    }

    /**
     * @return Mana_Db_Resource_Entity_JsonCollection
     */
    protected function _renderFilters() {
        $this->_data = array_filter($this->_data, array($this, '_filterItem'));
        return $this;
    }

    /**
     * Render sql select orders
     *
     * @return  Mana_Db_Resource_Entity_JsonCollection
     */
    protected function _renderOrders() {
        if (count($this->_orders)) {
            uasort($this->_data, array($this, '_compareItems'));
        }

        return $this;
    }

    /**
     * Render sql select limit
     *
     * @return  Mana_Db_Resource_Entity_JsonCollection
     */
    protected function _renderLimit() {
        if ($this->_pageSize !== false) {
            $offset = ($this->_curPage - 1) * $this->_pageSize;
            $count = count($this->_data);
            if ($count > $offset) {
                if ($count >= $offset + $this->_pageSize) {
                    $this->_data = array_slice($this->_data, $offset, $this->_pageSize);
                }
                else {
                    $this->_data = array_slice($this->_data, $offset);
                }
            }
            else {
                $this->_data = array();
            }
        }

        return $this;
    }

    public function getRawData() {
        return $this->_rawData;
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _compareItems($a, $b) {
        foreach ($this->_orders as $column => $direction) {
            if (isset($a[$column])) {
                if (isset($b[$column])) {
                    if ($a[$column] < $b[$column]) {
                        return strtolower($direction) == 'desc' ? 1 : -1;
                    }
                    if ($a[$column] > $b[$column]) {
                        return strtolower($direction) == 'desc' ? -1 : 1;
                    }
                }
                else {
                    return strtolower($direction) == 'desc' ? -1 : 1;
                }
            }
            else {
                if (isset($b[$column])) {
                    return strtolower($direction) == 'desc' ? 1 : -1;
                }
            }
        }
        return 0;
    }

    /**
     * @param array $a
     * @return bool
     */
    protected function _filterItem($a) {
        foreach ($this->_mFilters as $filter) {
            $value = isset($a[$filter['attribute']]) ? $a[$filter['attribute']] : '';
            if (isset($filter['condition']['like'])) {
                $test = $filter['condition']['like'];
                if ($this->getMbstring()->stripos($value, $this->getMbstring()->substr($test, 1, mb_strlen($test) - 2)) === false) {
                    return false;
                }
            }
            elseif (isset($filter['condition']['eq'])) {
                $test = $filter['condition']['eq'];
                if ($value != $test) {
                    return false;
                }
            }
        }

        return true;
    }
    public function addFieldToFilter($attribute, $condition = null) {
        $this->_mFilters[] = array('attribute' => $attribute, 'condition' => $condition);
    }

    #region Dependencies

    /**
     * @return Mana_Core_Helper_Mbstring
     */
    public function getMbstring() {
        return Mage::helper('mana_core/mbstring');
    }
    #endregion
}