<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for Mana_Db module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Db_Helper_Data extends Mage_Core_Helper_Abstract {
	protected $_logQueries = false;
    protected $_resourceSingletons = array();

	public function getLogQueries() {
		return Mage::getStoreConfig('mana_db/replicate/log_queries');
	}
	public function getLogActions() {
		return Mage::getStoreConfig('mana_db/replicate/log_actions');
	}
	public function getSkipInserts() {
		return Mage::getStoreConfig('mana_db/replicate/skip_inserts');
	}
	public function getSkipUpdates() {
		return Mage::getStoreConfig('mana_db/replicate/skip_updates');
	}
	public function getSkipDeletes() {
		return Mage::getStoreConfig('mana_db/replicate/skip_deletes');
	}
	public function getBatchSize() {
    $result = Mage::getStoreConfig('mana_db/replicate/batch_size');
		return $result ? $result : 10000;
	}
	public function getNoTransaction() {
		return Mage::getStoreConfig('mana_db/replicate/no_transaction');
	}
	public function getMaxExecutionTime() {
		return Mage::getStoreConfig('mana_db/replicate/max_execution_time');
	}
	public function getMemoryLimit() {
		return Mage::getStoreConfig('mana_db/replicate/memory_limit');
	}
	public function logQuery($action, $query) {
		if ($this->getLogQueries()) {
			Mage::log($action.': '.(string)$query, Zend_Log::DEBUG, 'replicate.log');
		}
	}
	public function logAction($action, $object) {
		if ($this->getLogActions()) {
			Mage::log($action.': '.$object->toJson(), Zend_Log::DEBUG, 'replicate.log');
		}
	}
	public function hasOverriddenValue($object, $values, $bit) {
		$field = 'default_mask'.$this->getMaskIndex($bit);
		if ($object->hasData($field)) {
			return ($object->getData($field) & $this->getMask($bit)) != 0;
		}
		else {
			return ($values[$field] & $this->getMask($bit)) != 0;
		}
	}
    public function hasOverriddenValueEx($object, $bit, $field = 'default_mask') {
        $field .= $this->getMaskIndex($bit);
        return ($object->getData($field) & $this->getMask($bit)) != 0;
    }
    public function getGlobalEntityName($entityName) {
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		if (!$core->endsWith($entityName, '_store')) {
			throw new Exception(Mage::helper('mana_db')->__('Store-level entity %s name should end with _store', $entityName));
		}
		return substr($entityName, 0, strlen($entityName) - strlen('_store'));
	}
	public function joinLeft($select, $alias, $table, $condition) {
		if (!array_key_exists($alias, $select->getPart(Zend_Db_Select::FROM))) {
			$select->joinLeft(array($alias => $table), $condition, null);
		}
		return $select;
	}
	public function getMaskIndex($bit) {
		return ((int)floor($bit / 32));
	}
	public function getMask($bit) {
		return 1 << ($bit % 32); 
	}
	public function updateDefaultableField($model, $fieldName, $bit, $fields, $useDefault) {
		if ($useDefault && in_array($fieldName, $useDefault)) {
			$model->setData('default_mask'.$this->getMaskIndex($bit), 
				$model->getData('default_mask'.$this->getMaskIndex($bit)) & ~ $this->getMask($bit));
		}
		elseif ($fields && isset($fields[$fieldName])) {
			$model->setData('default_mask'.$this->getMaskIndex($bit), 
				$model->getData('default_mask'.$this->getMaskIndex($bit)) | $this->getMask($bit));
			$model->setData($fieldName, $fields[$fieldName]);
		}
	}
	/**
	 * Enter description here ...
	 * @param Varien_Db_Select $select
	 * @param string $column
	 */
	public function selectColumn($select, $column) {
		list($expr, $alias) = explode(' AS ', $column);
		foreach ($select->getPart(Zend_Db_Select::COLUMNS) as $columnInfo) {
			if ($columnInfo[2] == $alias) {
				return $this;
			}
		}
		$select->columns($column);
		return $this;
	}

	public function isReplicatedConfigChanged($config_data) {
		$result = new Varien_Object();
		Mage::dispatchEvent('m_db_is_config_changed', compact('result', 'config_data'));
		return $result->getIsChanged();
	}
	
	public function replicateObject($object, $filter) {
		$this->replicate(array(
			'object' => $object,
			'filter' => $filter,
			'trackKeys' => true,
			'transaction' => false,
		));
	}
	public function replicate($options = array()) {
		$options = array_merge(array(
			'db' => Mage::getSingleton('core/resource')->getConnection('core/write'),
    		'trackKeys' => false,
    		'transaction' => !$this->getNoTransaction(),
			'filter' => array(),
			'batchSize' => $this->getBatchSize(),
			'object' => null,
		), $options);
    if ($this->getMaxExecutionTime()) {
        ini_set('max_execution_time', $this->getMaxExecutionTime());
    }
    if ($this->getMemoryLimit()) {
        ini_set('memory_limit', $this->getMemoryLimit());
    }
		if (count($options['filter']) == 0) {
		    $options['db']->resetDdlCache();
        }
		if ($options['transaction']) {
			$options['db']->beginTransaction();
		}
		try {
			$options['targets'] = $this->_getAllReplicationTargets();
	    	foreach ($options['targets'] as $entityName => /* @var $target Mana_Db_Model_Replication_Target */ $target) {
	    		// assign saved/deleted keys to track
	    		if ($options['trackKeys']) {
	    			if (isset($options['filter'][$entityName])) {
	    				if (isset($options['filter'][$entityName]['saved'])) {
	    					foreach ($options['filter'][$entityName]['saved'] as $key) {
	    						$target->setSavedKey($key, $key);
	    					}
	    				}
	    				if (isset($options['filter'][$entityName]['deleted'])) {
	    					foreach ($options['filter'][$entityName]['deleted'] as $key) {
	    						$target->setDeletedKey($key, $key);
	    					}
	    				}
	    			}
	    		}
	    		
	    		if ($target->getReplicable() && (!$options['object'] || $options['object']->getEntityName() == $entityName)) {
	    			$model = Mage::getResourceSingleton($entityName);
	    			
		    		// update existing rows
          if (!$this->getSkipUpdates()) {
  					$target->setSelects(array())->setIsKeyFilterApplied(false);
  		    		$model->prepareReplicationUpdateSelects($target, $options);
  					if (count($target->getSelects()) && (!$options['trackKeys'] || $target->getIsKeyFilterApplied())) {
  						foreach ($target->getSelects() as $select) {
  							$offset = 0;
  							$this->logQuery('UPDATE', $select);
  							do {
  								$sourceData = $options['db']->fetchAll($select->limit($options['batchSize'], $offset));
  								if ($sourceData && count($sourceData)) {
  									foreach ($sourceData as $values) {
  										if ($object = $model->processReplicationUpdate($values, $options)) {
  											if ($object != $options['object']) {
  												$this->logAction('UPDATE', $object);
  												$object->save();
  												if ($options['trackKeys']) {
  													$target->setSavedKey($object->getId(), $object->getId());
  												}
  											}
  											else {
  												$object->unsetData('_m_prevent_replication');
  											}
  										}
  									}
  								}
  								$offset += $options['batchSize'];
  							} while ($sourceData && count($sourceData));
  						}
  					}
					}
					// insert rows
          if (!$this->getSkipInserts()) {
  					$target->setSelects(array())->setIsKeyFilterApplied(false);
  		    		$model->prepareReplicationInsertSelects($target, $options);
  					if (count($target->getSelects()) && (!$options['trackKeys'] || $target->getIsKeyFilterApplied())) {
  						foreach ($target->getSelects() as $select) {
  							$offset = 0;
  							$this->logQuery('INSERT', $select);
  							do {
  								$sourceData = $options['db']->fetchAll($select->limit($options['batchSize']));
  								if ($sourceData && count($sourceData)) {
  									foreach ($sourceData as $values) {
  										if ($object = $model->processReplicationInsert($values, $options)) {
  											if ($object != $options['object']) {
  												$this->logAction('INSERT', $object);
  												$object->save();
  												if ($options['trackKeys']) {
  													$target->setSavedKey($object->getId(), $object->getId());
  												}
  											}
  											else {
  												$object->unsetData('_m_prevent_replication');
  											}
  										}
  									}
  								}
  								$offset += $options['batchSize'];
  							} while ($sourceData && count($sourceData));
  						}
  					}
					}
					// delete rows
          if (!$this->getSkipDeletes()) {
  					$target->setSelects(array())->setIsKeyFilterApplied(false);
  		    		$model->prepareReplicationDeleteSelects($target, $options);
  					if (count($target->getSelects()) && (!$options['trackKeys'] || $target->getIsKeyFilterApplied())) {
  						foreach ($target->getSelects() as $select) {
  							$offset = 0;
  							$this->logQuery('DELETE', $select);
  							do {
  								$ids = $options['db']->fetchCol($select->limit($options['batchSize']));
  								if ($ids && count($ids)) {
  									$model->processReplicationDelete($ids, $options);
  									if ($options['trackKeys']) {
  										foreach ($ids as $id) {
  											$target->setDeletedKey($id, $id);
  										}
  									}
  								}
  								$offset += $options['batchSize'];
  							} while ($ids && count($ids));
  						}
  					}
  	    		}
  	    	}
        }

			if ($options['transaction']) {
				$options['db']->commit();
			}
		}
		catch (Exception $e) {
			if ($options['transaction']) {
				$options['db']->rollback();
			}
			throw $e;
		}
		return $this;
	}
	
	/**
	 * Enter description here ...
	 * @param Varien_Object $result
	 * @param Mage_Core_Model_Config_Data $configData
	 * @param array $paths
	 */
	public function checkIfPathsChanged($result, $configData, $paths) {
		$storeId = $configData->getStoreCode() ? Mage::app()->getStore($configData->getStoreCode())->getId() : 0;
		$this->_changingValues[$storeId.'/'.$configData->getPath()] = $configData->getValue();
		if (!$result->getIsChanged() && in_array($configData->getPath(), $paths) && 
			Mage::getStoreConfig($configData->getPath(), $storeId) != $configData->getValue()) 
		{
			$result->setIsChanged(true);
		}
	}
	protected $_changingValues = array();
	public function getLatestConfig($path, $storeId = 0) {
		return isset($this->_changingValues[$storeId.'/'.$path])
			? $this->_changingValues[$storeId.'/'.$path]
			: Mage::getStoreConfig($path, $storeId);
	}
	protected function _getAllReplicationTargets() {
		$result = array();
		foreach (Mage::getConfig()->getNode()->global->models->children() as $module) {
			if (isset($module->resourceModel)) {
				$resourceModel = (string)$module->resourceModel;
				if (isset(Mage::getConfig()->getNode()->global->models->$resourceModel->entities)) {
					foreach (Mage::getConfig()->getNode()->global->models->$resourceModel->entities->children() as $entity) {
						if (!empty($entity->replicable)) {
							$model = $module->getName().'/'.$entity->getName();
							if (!isset($result[$model])) {
								$result[$model] = Mage::getModel('mana_db/replication_target')->setEntityName($model);
							}
							$result[$model]->setReplicable(true);
							foreach (Mage::getResourceSingleton($model)->getReplicationSources() as $source) {
								if (!$result[$model]->hasSourceEntityName($source)) {
									$result[$model]->setSourceEntityName($source, $source);
								}
								if (!isset($result[$source])) {
									$result[$source] = Mage::getModel('mana_db/replication_target')->setEntityName($source);
								}
								
							}
						}
					}
				}
			}
		}
		
        $count = count($result);
        $orders = array();
        for ($position = 0; $position < $count; $position++) {
            $found = false;
            foreach ($result as $targetName => $target) {
                if (!isset($orders[$targetName])) {
                    $hasUnresolvedDependency = false;
                    foreach ($target->getSourceEntityNames() as $dependency) {
                        if (!isset($orders[$dependency])) {
                            // $dependency not yet sorted so $module should wait until that happens 
                            $hasUnresolvedDependency = true;
                            break;
                        }
                    }
                    if (!$hasUnresolvedDependency) {
                        $found = $targetName;
                        break;
                    }
                }
            }
            if ($found) {
                $orders[$found] = count($orders);
            }
            else {
                $circular = array();
                foreach ($result as $targetName => $target) {
                    if (!isset($orders[$targetName])) {
                        $circular[] = $targetName;
                    }
                }
                throw new Exception(Mage::helper('mana_db')->__('Entities with circular dependencies found: %s', implode(', ', $circular)));
            }
        }
        $this->_orders = $orders;
        uasort($result, array($this, '_sortReplicationTargetCallback'));
		
		return $result;
	}
	protected $_orders;
	public function _sortReplicationTargetCallback($a, $b) {
		$a = $this->_orders[$a->getEntityName()];
        $b = $this->_orders[$b->getEntityName()];
        if ($a == $b) return 0;
        return $a < $b ? -1 : 1;
	}

    public function isEditingSessionExpired($editSessionId) {
        return Mage::getResourceSingleton('mana_db/edit_session')->isExpired($editSessionId);
    }
    protected $_inEditing = false;
    public function getInEditing() {
        return $this->_inEditing;
    }
    public function setInEditing($value = true) {
        $this->_inEditing = $value;
        return $this;
    }
	public function beginEditing() {
        $this->_lastEditSessionId = Mage::getResourceSingleton('mana_db/edit_session')->begin();
        return $this->_lastEditSessionId;
	}
	protected $_lastEditSessionId = false;
	public function getLastEditingSessionId() {
        return $this->_lastEditSessionId;
    }
    public function beginEditingIfNotAlreadyDoneSo() {
        if (!Mage::helper('mana_db')->getInEditing()) {
            Mage::helper('mana_db')->setInEditing();
            return $this->beginEditing();
        }
        else {
            return $this->_lastEditSessionId;
        }
    }
    public function emptyEdit($editSessionId) {
        return array(
            'sessionId' => $editSessionId,
            'pending' => array(),
            'saved' => array(),
            'deleted' => array(),
        );
    }

    protected $_resourceSuffixes = array('_collection');
    public function getSuffix ($entityName, $suffixes) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        foreach ($suffixes as $candidateSuffix) {
            if ($core->endsWith($entityName, $candidateSuffix)) {
                return $candidateSuffix;
            }
        }
        return '';
    }
    public function getScopedName($entityName) {
        if ($suffix = $this->getSuffix($entityName, $this->_resourceSuffixes)) {
            $entityName = substr($entityName, 0, strlen($entityName) - strlen($suffix));
        }

        $parts = explode('/', $entityName);
        if (count($parts) > 2) {
            if ($parts[2] == 'global') {
                $entityName = "{$parts[0]}/{$parts[1]}";
            }
            else {
                $entityName = "{$parts[0]}/{$parts[1]}_{$parts[2]}";
            }
        }
        return $entityName . $suffix;
    }

    /**
     * @param string $entityName
     * @param array|null $arguments
     * @return Mana_Db_Resource_Entity_Collection | Mana_Db_Resource_Entity
     */
    public function getResourceModel($entityName, $arguments = null) {
        if ($suffix = $this->getSuffix($entityName, $this->_resourceSuffixes)) {
            $entityName = substr($entityName, 0, strlen($entityName) - strlen($suffix));
        }

        $arguments = array_merge(array(
            'scope' => $entityName,
        ), $arguments ? (is_array($arguments) ? $arguments : array('resource' => $arguments)) : array());


        $arguments = array_merge(array('scope' => $entityName), $arguments);

        $resolvedEntityName = $this->getScopedName($entityName);
        if ($this->resourceExists($resolvedEntityName . $suffix)) {
            return Mage::getResourceModel($resolvedEntityName . $suffix, $arguments);
        }

        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');
        if (!($scopeXml = $dbConfig->getScopeXml($entityName))) {
            return Mage::getResourceModel('mana_db/entity' . $suffix, $arguments);
        }

        if (!empty($scopeXml->flattens)) {
            $entityName = (string)$scopeXml->flattens;
            $resolvedEntityName = $this->getScopedName($entityName);
            if ($this->resourceExists($resolvedEntityName . $suffix)) {
                return Mage::getResourceModel($resolvedEntityName . $suffix, $arguments);
            }
            $scopeXml = $dbConfig->getScopeXml($entityName);
        }
        if (!empty($scopeXml->store_specifics_for)) {
            $entityName = (string)$scopeXml->store_specifics_for;
            $resolvedEntityName = $this->getScopedName($entityName);
            if ($this->resourceExists($resolvedEntityName . $suffix)) {
                return Mage::getResourceModel($resolvedEntityName . $suffix, $arguments);
            }
        }

        return Mage::getResourceModel('mana_db/entity' . $suffix, $arguments);
    }

    /**
     * @param string $entityName
     * @param array | null $arguments
     * @return Mana_Db_Resource_Entity
     */
    public function getResourceSingleton($entityName, $arguments = null) {
        if ($suffix = $this->getSuffix($entityName, $this->_resourceSuffixes)) {
            $entityNameWithoutSuffix = substr($entityName, 0, strlen($entityName) - strlen($suffix));
        }
        else {
            $entityNameWithoutSuffix = $entityName;
        }

        $arguments = array_merge(array(
            'scope' => $entityNameWithoutSuffix,
        ), $arguments ? (is_array($arguments) ? $arguments : array('resource' => $arguments)) : array());

        $resolvedEntityName = $this->getScopedName($entityName);
        if ($this->resourceExists($resolvedEntityName)) {
            return Mage::getResourceSingleton($resolvedEntityName, $arguments);
        }
        else {
            if (!isset($this->_resourceSingletons[$resolvedEntityName])) {
                $this->_resourceSingletons[$resolvedEntityName] = $this->getResourceModel($entityName, $arguments);
            }
            return $this->_resourceSingletons[$resolvedEntityName];
        }
    }
    /**
     * @param string $entityName
     * @param array $arguments
     * @return Mana_Db_Model_Entity
     */
    public function getModel($entityName, $arguments = array()) {
        $arguments = array_merge(array('scope' => $entityName), $arguments);

        $resolvedEntityName = $this->getScopedName($entityName);
        if ($this->modelExists($resolvedEntityName)) {
            return Mage::getModel($resolvedEntityName, $arguments);
        }

        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');
        if (!($scopeXml = $dbConfig->getScopeXml($entityName))) {
            return Mage::getModel('mana_db/entity', $arguments);
        }

        if (!empty($scopeXml->flattens)) {
            $entityName = (string)$scopeXml->flattens;
            $resolvedEntityName = $this->getScopedName($entityName);
            if ($this->modelExists($resolvedEntityName)) {
                return Mage::getModel($resolvedEntityName, $arguments);
            }
            $scopeXml = $dbConfig->getScopeXml($entityName);
        }
        if (!empty($scopeXml->store_specifics_for)) {
            $entityName = (string)$scopeXml->store_specifics_for;
            $resolvedEntityName = $this->getScopedName($entityName);
            if ($this->modelExists($resolvedEntityName)) {
                return Mage::getModel($resolvedEntityName, $arguments);
            }
        }
        return Mage::getModel('mana_db/entity', $arguments);
    }

    public function resourceExists($entityName) {
        if ($className = Mage::getConfig()->getResourceModelClassName($entityName)) {
            return $this->classExists($className);
        }
        else {
            return false;
        }

    }

    public function modelExists($entityName) {
        if ($className = Mage::getConfig()->getModelClassName($entityName)) {
            return $this->classExists($className);
        }
        else {
            return false;
        }

    }

    public function classExists($class) {
        if (defined('COMPILER_INCLUDE_PATH')) {
            $classFile = $class;
        }
        else {
            $classFile = str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('_', ' ', $class)));
        }
        $classFile .= '.php';
        foreach (explode(PS, get_include_path()) as $path) {
            if (@file_exists($path.DS.$classFile)) {
                return true;
            }
        }

        return false;
    }
}