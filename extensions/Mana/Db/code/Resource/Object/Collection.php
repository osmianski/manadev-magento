<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class Mana_Db_Resource_Object_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	public function getEntityName() {
		return $this->getResourceModelName();
	}
	protected function _beforeLoad() {
		if (!count($this->_columns) || isset($this->_columns['*'])) {
			$this->getSelect()->columns('main_table.*');
			$this->addVirtualColumns($this->getSelect());
		}
		else {
			$virtualColumns = $this->addVirtualColumns($this->getSelect(), $this->_columns);
			foreach (array_diff($this->_columns, $virtualColumns) as $column) {
				$this->getSelect()->columns('main_table.'.$column);
			}
		}
		$this->_renderEditFilter();
		parent::_beforeLoad();
		return $this;
	}
	public function addStoreFilter($store) {
        $this->addFieldToFilter('store_id', $store->getId());
        return $this;
    }
    
	protected $_columns = array();
	public function addColumnToSelect($column) {
		if (is_array($column)) {
			foreach ($column as $item) {
				$this->_columns[$item] = $item;
			}
		}
		else {
			$this->_columns[$column] = $column;
		}
		return $this;
	}
	public function addVirtualColumns($select, $columns = null) {
		$result = Mage::getModel('mana_db/virtual_result');
		$this->_addVirtualColumns($result, $select, $columns);
		$entity_name = $this->getEntityName();
		Mage::dispatchEvent('m_db_virtual_collection_columns', compact('entity_name', 'result', 'select', 'columns'));
		return $result->getColumns();
	}
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Virtual_Result $result
	 * @param Varien_Db_Select $select
	 * @param array $columns
	 */
	protected function _addVirtualColumns($result, $select, $columns = null) {
	}
    protected $_editFilter = null;
    protected $_parentCondition = '';
    public function setEditFilter($editFilter, $parentCondition = '') {
        $this->_editFilter = $editFilter;
        $this->_parentCondition = $parentCondition;
        return $this;
    }
    protected function _renderEditFilter() {
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
                $sql = "($sql)".
                    " AND ({$this->getConnection()->quoteInto("$alias.id NOT IN (?)", $this->_editFilter['deleted'])})";
                    " AND ({$this->getConnection()->quoteInto("$alias.edit_status NOT IN (?)", $this->_editFilter['deleted'])})";
            }
            $this->getSelect()->where($sql);
        }
        elseif ($this->_editFilter) {
            $this->getSelect()->where("$alias.edit_status = 0");
        }
    }
}