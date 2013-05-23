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
	public function getLogQueries() {
		return $this->_logQueries;
	}
	public function logQuery($action, $query) {
		if ($this->getLogQueries()) {
			Mage::log($action.': '.(string)$query, Zend_Log::DEBUG, 'replicate.log');
		}
	}
	public function logAction($action, $object) {
		if ($this->getLogQueries()) {
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
    		'transaction' => true,
			'filter' => array(),
			'batchSize' => 10000,
			'object' => null, 
		), $options);
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
					
					// insert rows
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
					
					// delete rows
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
}