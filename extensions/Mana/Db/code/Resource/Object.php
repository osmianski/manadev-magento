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
abstract class Mana_Db_Resource_Object extends Mage_Core_Model_Mysql4_Abstract {
	protected $_entityName;
	protected function _init($mainTable, $idFieldName) {
		parent::_init($mainTable, $idFieldName);
		$this->_entityName = $mainTable;
	}
	public function getEntityName() {
		return $this->_entityName;
	}
	
	public function loadByGlobalId($object, $globalId, $storeId) {
        $read = $this->_getReadAdapter();

        /* @var $select Varien_Db_Select */ $select = $this->_getLoadSelect('global_id', $globalId, $object);
		$select->where($this->getMainTable().'.store_id'.'=?', $storeId);        
        $data = $read->fetchRow($select);
        if ($data) {
        	$object->setData($data);
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
	}
	
	protected function _getLoadSelect($field, $value, $object) {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), null)
            ->where($this->getMainTable().'.'.$field.'=?', $value);
        
		if (!count($this->_columns) || isset($this->_columns['*'])) {
			$select->columns($this->getMainTable().'.*');
			$this->addVirtualColumns($select);
		}
		else {
			$virtualColumns = $this->addVirtualColumns($select, $this->_columns);
			foreach (array_diff($this->_columns, $virtualColumns) as $column) {
				$this->getSelect()->columns($this->getMainTable().'.'.$column);
			}
		}
		
        return $select;
    }
    
	public function getReplicationSources() {
		$result = new Varien_Object(array('sources' => $this->_getReplicationSources()));
		$targetName = $this->getEntityName();
		Mage::dispatchEvent('m_db_sources', compact('targetName', 'result'));
		return $result->getSources();
	}
	protected function _getReplicationSources() {
		return array();
	}
	
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Replication_Target $target
	 */
	public function prepareReplicationUpdateSelects($target, $options) {
		$this->_prepareReplicationUpdateSelects($target, $options);
		Mage::dispatchEvent('m_db_update_tables', compact('target', 'options'));
		Mage::dispatchEvent('m_db_update_columns', compact('target', 'options'));
	}
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Replication_Target $target
	 */
	protected function _prepareReplicationUpdateSelects($target, $options) {
	}
	
	/**
	 * Enter description here ...
	 * @param array $values
	 * @param array $options
	 */
	public function processReplicationUpdate($values, $options) {
		$object = $options['object'] ? $options['object'] : Mage::getModel($this->getEntityName());
		$this->_processReplicationUpdate($object, $values, $options);
		Mage::dispatchEvent('m_db_update_process', compact('object', 'values', 'options'));
		return $object;
	}
	
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Object $object
	 * @param array $values
	 * @param array $options
	 */
	protected function _processReplicationUpdate($object, $values, $options) {
	}

	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Replication_Target $target
	 */
	public function prepareReplicationInsertSelects($target, $options) {
		$this->_prepareReplicationInsertSelects($target, $options);
		Mage::dispatchEvent('m_db_insert_tables', compact('target', 'options'));
		Mage::dispatchEvent('m_db_insert_columns', compact('target', 'options'));
	}
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Replication_Target $target
	 */
	protected function _prepareReplicationInsertSelects($target, $options) {
	}
	
	/**
	 * Enter description here ...
	 * @param array $values
	 * @param array $options
	 */
	public function processReplicationInsert($values, $options) {
		$object = $options['object'] ? $options['object'] : Mage::getModel($this->getEntityName());
		$this->_processReplicationInsert($object, $values, $options);
		Mage::dispatchEvent('m_db_insert_process', compact('object', 'values', 'options'));
		return $object;
	}
	
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Object $object
	 * @param array $values
	 * @param array $options
	 */
	protected function _processReplicationInsert($object, $values, $options) {
	}

	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Replication_Target $target
	 */
	public function prepareReplicationDeleteSelects($target, $options) {
		$this->_prepareReplicationDeleteSelects($target, $options);
		Mage::dispatchEvent('m_db_delete_tables', compact('target', 'options'));
		Mage::dispatchEvent('m_db_delete_columns', compact('target', 'options'));
	}
	/**
	 * Enter description here ...
	 * @param Mana_Db_Model_Replication_Target $target
	 */
	protected function _prepareReplicationDeleteSelects($target, $options) {
	}
	
	/**
	 * Enter description here ...
	 * @param array $values
	 * @param array $options
	 */
	public function processReplicationDelete($values, $options) {
		$this->_processReplicationDelete($values, $options);
		$entity_name = $this->getEntityName();
		Mage::dispatchEvent('m_db_delete_process', compact('entity_name', 'values', 'options'));
	}
	
	/**
	 * Enter description here ...
	 * @param array $values
	 * @param array $options
	 */
	protected function _processReplicationDelete($values, $options) {
	}

	protected $_columns = array();
	public function addColumnToSelect($column) {
		$this->_columns[$column] = $column;
		return $this;
	}
	public function addVirtualColumns($select, $columns = null) {
		$result = Mage::getModel('mana_db/virtual_result');
		$this->_addVirtualColumns($result, $select, $columns);
		$entity_name = $this->getEntityName();
		Mage::dispatchEvent('m_db_virtual_columns', compact('entity_name', 'result', 'select', 'columns'));
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

	public function addEditedData($object, $fields, $use_default) {
		$this->_addEditedData($object, $fields, $use_default);
		Mage::dispatchEvent('m_db_add_edited_data', compact('object', 'fields', 'use_default'));
	}
	protected function _addEditedData($object, $fields, $useDefault) {
	}
    public function addEditedDetails($object, $request) {
        $this->_addEditedDetails($object, $request);
        Mage::dispatchEvent('m_db_add_edited_details', compact('object', 'request'));
    }
    protected function _addEditedDetails($object, $request) {
    }
}