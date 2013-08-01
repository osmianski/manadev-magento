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
class Mana_Db_Resource_Entity_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected $_scope;
    protected $_editFilter = null;
    protected $_parentCondition = '';

    public function __construct($resource = null) {
        if (is_array($resource)) {
            if (isset($resource['scope'])) {
                $this->_scope = $resource['scope'];
            }
            $resource = isset($resource['resource']) ? $resource['resource'] : null;
        }

        parent::__construct($resource);
    }

    protected function _construct() {
        $this->_initScope();
    }
    protected function _initScope() {

        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        $this->_init($this->_scope);
        return $this;
    }

    /**
     * Get resource instance
     *
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    public function getResource() {
        if (empty($this->_resource)) {
            /* @var $db Mana_Db_Helper_Data */
            $db = Mage::helper('mana_db');

            $this->_resource = $db->getResourceSingleton($this->getResourceModelName());
        }

        return $this->_resource;
    }

    public function getNewEmptyItem() {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        return $db->getModel($this->_model);
    }

    public function setEditFilter($editFilter, $parentCondition = '') {
        $this->_editFilter = $editFilter;
        $this->_parentCondition = $parentCondition;

        return $this;
    }

    public function setStoreFilter($storeId = null) {
        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }

        $this->getSelect()->where("`main_table`.`store_id` = ?", $storeId);
        return $this;
    }

    protected function _beforeLoad() {
        $this->_renderEditFilter();
        parent::_beforeLoad();

        return $this;
    }

    protected function _renderEditFilter($select = null) {
        if (!$select) {
            $select = $this->getSelect();
        }
        $alias = 'main_table';
        if (is_array($this->_editFilter)) {
            $sql = count($this->_editFilter['saved'])
                ? $this->getConnection()->quoteInto("$alias.edit_status = 0 AND $alias.id NOT IN (?)", array_keys($this->_editFilter['saved']))
                : "$alias.edit_status = 0";
            if ($this->_parentCondition) {
                $sql .= " AND ($alias.{$this->_parentCondition})";
            }
            if (count($this->_editFilter['saved'])) {
                $sql = "($sql) OR ({$this->getConnection()->quoteInto(
                    "$alias.edit_status <> 0 AND $alias.edit_session_id = ?", $this->_editFilter['sessionId'])})";
            }
            if (count($this->_editFilter['deleted'])) {
                $sql = "($sql)" .
                    " AND ({$this->getConnection()->quoteInto("$alias.id NOT IN (?)", $this->_editFilter['deleted'])})";
                //" AND ({$this->getConnection()->quoteInto("$alias.edit_status NOT IN (?)", $this->_editFilter['deleted'])})";
            }
            $select->where($sql);
        }
        elseif ($this->_editFilter) {
            $select->where("$alias.edit_status = 0");
            if ($this->_parentCondition) {
                $select->where("$alias.{$this->_parentCondition}");
            }
        }

        return $this;
    }

    public function getSelectCountSql() {
        $sql = parent::getSelectCountSql();
        $this->_renderEditFilter($sql);
        return $sql;
    }
}