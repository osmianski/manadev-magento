<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Base class for DB-backed collections
 * @author Mana Team
 *
 */
class Mana_Core_Resource_Eav_Collection extends Mage_Eav_Model_Entity_Collection_Abstract {
    protected $_storeId = null;

    public function setStore($store)
    {
        $this->setStoreId(Mage::app()->getStore($store)->getId());
        return $this;
    }

    public function setStoreId($storeId)
    {
        if ($storeId instanceof Mage_Core_Model_Store) {
            $storeId = $storeId->getId();
        }
        $this->_storeId = $storeId;
        return $this;
    }

    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }
        return $this->_storeId;
    }

	public function addStoreFilter($store=null)
    {
        if (is_null($store)) {
            $store = $this->getStoreId();
        }
        $store = Mage::app()->getStore($store);

        if (!$store->isAdmin()) {
            $this->setStoreId($store);
        }

        return $this;
    }

    public function get($fields) {
    	$result = array();
    	foreach ($fields as $field) {
    		$result[$field] = $this->$field;
    	}
    	return $result;
    }
    
    public function set($values) {
    	foreach ($values as $field => $value) {
    		$this->$field = $value;
    	}
    	return $this;
    }
	public function load($printQuery = false, $logQuery = false) {
        if ($this->isLoaded()) {
            return $this;
        }
		parent::load($printQuery, $logQuery);
		if ($this->_items) {
			foreach ($this->_items as $index => $item) {
    			if ($this->_isValidItem($item)) {
					$item->setStoreId($this->_storeId);
					$item->loadDefaults();
    			}
    			else {
    				unset($this->_items[$index]);
    			}
			}
		}
		return $this;
	}
    protected function _isValidItem($item) {
    	return true;
    }
	protected function _getLoadAttributesSelect($table, $attributeIds = array())
    {
        if (empty($attributeIds)) {
            $attributeIds = $this->_selectAttributes;
        }
        if ((int) $this->getStoreId()) {
            $entityIdField = $this->getEntity()->getEntityIdField();
            $joinCondition = 'store.attribute_id=default.attribute_id
                AND store.entity_id=default.entity_id
                AND store.store_id='.(int) $this->getStoreId();

            $select = $this->getConnection()->select()
                ->from(array('default'=>$table), array($entityIdField, 'attribute_id', 'default_value'=>'value'))
                ->joinLeft(
                    array('store'=>$table),
                    $joinCondition,
                    array(
                        'store_value' => 'value',
                        'value' => new Zend_Db_Expr('IF(store.value_id>0, store.value, default.value)')
                    )
                )
                ->where('default.entity_type_id=?', $this->getEntity()->getTypeId())
                ->where("default.$entityIdField in (?)", array_keys($this->_itemsById))
                ->where('default.attribute_id in (?)', $attributeIds)
                ->where('default.store_id = 0');
        }
        else {
	        if (empty($attributeIds)) {
	            $attributeIds = $this->_selectAttributes;
	        }
	        $entityIdField = $this->getEntity()->getEntityIdField();
	        $select = $this->getConnection()->select()
	            ->from($table, array($entityIdField, 'attribute_id', 'value'))
	            ->where('entity_type_id =?', $this->getEntity()->getTypeId())
	            ->where("$entityIdField IN (?)", array_keys($this->_itemsById))
	            ->where('attribute_id IN (?)', $attributeIds)
                ->where('store_id=?', $this->getDefaultStoreId());
        }
        return $select;
    }
    protected function _joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias)
    {
        if (isset($this->_joinAttributes[$fieldCode]['store_id'])) {
            $store_id = $this->_joinAttributes[$fieldCode]['store_id'];
        }
        else {
            $store_id = $this->getStoreId();
        }

        if ($store_id != $this->getDefaultStoreId() && $attribute->getIsGlobal() != Mana_Core_Model_Attribute_Scope::_GLOBAL) {
            /**
             * Add joining default value for not default store
             * if value for store is null - we use default value
             */
            $defCondition = '('.join(') AND (', $condition).')';
            $defAlias     = $tableAlias.'_default';
            $defFieldCode = $fieldCode.'_default';
            $defFieldAlias= str_replace($tableAlias, $defAlias, $fieldAlias);

            $defCondition = str_replace($tableAlias, $defAlias, $defCondition);
            $defCondition.= $this->getConnection()->quoteInto(" AND $defAlias.store_id=?", $this->getDefaultStoreId());

            $this->getSelect()->$method(
                array($defAlias => $attribute->getBackend()->getTable()),
                $defCondition,
                array()
            );

            $method = 'joinLeft';
            $fieldAlias = new Zend_Db_Expr("IF($tableAlias.value_id>0, $fieldAlias, $defFieldAlias)");
            $this->_joinAttributes[$fieldCode]['condition_alias'] = $fieldAlias;
            $this->_joinAttributes[$fieldCode]['attribute']       = $attribute;
        }
        else {
            $store_id = $this->getDefaultStoreId();
        }
        $condition[] = $this->getConnection()->quoteInto("$tableAlias.store_id=?", $store_id);
        return parent::_joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias);
    }
    public function getDefaultStoreId()
    {
        return Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
    }

    public function _loadAttributes($printQuery = false, $logQuery = false)
    {
        if (empty($this->_items) || empty($this->_itemsById) || empty($this->_selectAttributes)) {
            return $this;
        }

        $entity = $this->getEntity();
        $entityIdField = $entity->getEntityIdField();

        $tableAttributes = array();
        foreach ($this->_selectAttributes as $attributeCode => $attributeId) {
            if (!$attributeId) {
                continue;
            }
            $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($entity->getType(), $attributeCode);
            if ($attribute && !$attribute->isStatic()) {
                $tableAttributes[$attribute->getBackendTable()][] = $attributeId;
            }
        }

        $selects = array();
        foreach ($tableAttributes as $table=>$attributes) {
            $selects[] = $this->_getLoadAttributesSelect($table, $attributes);
        }
        if (!empty($selects)) {
            try {
                $select = implode(' UNION ', $selects);
                $values = $this->_fetchAll($select);
            } catch (Exception $e) {
                Mage::printException($e, $select);
                $this->printLogQuery(true, true, $select);
                throw $e;
            }

            foreach ($values as $value) {
                $this->_setItemAttributeValue($value);
            }
        }
        return $this;
    }
}