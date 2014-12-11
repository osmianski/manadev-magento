<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Db_Model_Entity_Indexer extends Mana_Core_Model_Indexer {
    protected $_code = 'mana_db';
    protected $_standardEventEntities = array(
        'core_store' => 'core/store',
    );
    /**
     * @var Varien_Object[]
     */
    protected $_matchedEvents = array();

    public function matchEvent(Mage_Index_Model_Event $event) {
        /* @var $object Mana_Db_Model_Entity */
        if (!($object = $event->getData('data_object'))) {
            return false;
        }

        $key = $event->getData('entity').'-'.$object->getId();
        if (!isset($this->_matchedEvents[$key])) {
            $this->_matchedEvents[$key] = $this->_matchEntity($event, $object);
        }
        return $this->_matchedEvents[$key]->getData('is_matching');
    }

    /**
     * @param Mage_Index_Model_Event $event
     * @param Mana_Db_Model_Entity $object
     * @return Varien_Object
     */
    protected function _matchEntity($event, /** @noinspection PhpUnusedParameterInspection */ $object) {
        $result = new Varien_Object(array('is_matching' => false));
        $entityFilters = array();
        $eventEntity = $event->getData('entity');
        if (isset($this->_standardEventEntities[$eventEntity])) {
            $eventEntity = $this->_standardEventEntities[$eventEntity];
        }
        foreach ($this->_sortTargetsByDependency() as $target) {
            if ($entity = (string)$target->entity) {
                foreach ($this->dbConfigHelper()->getEntityXml($entity)->scopes->children() as $scope) {
                    if (isset($scope->flattens)) {
                        if ($formula = $this->_findEntityFilterFormula($entity . '/' . $scope->getName(), $eventEntity)) {
                            $result->setData('is_matching', true);
                            $entityFilters[$entity . '/' . $scope->getName()] = $formula;
                        }
                    }
                }
            }
        }

        $result->setData('entity_filters', $entityFilters);
        return $result;
    }
    protected function _findEntityFilterFormula($entity, $what) {
        return $this->_findEntityFilterFormulaRecursively($entity, $what, '');
    }

    protected function _findEntityFilterFormulaRecursively($entity, $what, $formula) {
        if (!($scopeXml = $this->dbConfigHelper()->getScopeXml($entity))) {
            return false;
        }

        if (!isset($scopeXml->formula)) {
            return false;
        }

        foreach ($scopeXml->formula->children() as $selectXml) {
            if (isset($selectXml->from)) {
                $fromXml = $selectXml->from;
                foreach ($fromXml->children() as $alias => $definition) {
                    $sourceEntity = $alias == 'primary' ? (string)$scopeXml->flattens : (string)$definition->entity;
                    if ($sourceEntity == $what) {
                        return $formula . $alias . '.' . $this->_getPrimaryKey($sourceEntity);
                    }
                    else {
                        if ($result = $this->_findEntityFilterFormulaRecursively($sourceEntity, $what, $formula . $alias . '.')) {
                            return $result;
                        }
                    }
                }
            }

            if (isset($selectXml->join)) {
                $joinXml = $selectXml->join;
                /* @var $joinXml SimpleXMLElement */
                foreach ($joinXml->children() as $alias => $definition) {
                    $sourceEntity = $alias == 'primary' ? (string)$scopeXml->flattens : (string)$definition->entity;
                    if ($sourceEntity == $what) {
                        return $formula . $alias . '.' . $this->_getPrimaryKey($sourceEntity);
                    }
                    else {
                        if ($result = $this->_findEntityFilterFormulaRecursively($sourceEntity, $what, $formula . $alias . '.')) {
                            return $result;
                        }
                    }
                }
            }
        }
        return false;
    }

    protected function _getPrimaryKey($entity) {
        if ($this->dbConfigHelper()->getScopeXml($entity)) {
            return 'id';
        }
        else {
            return $this->tableProcessorHelper()->getPrimaryKey($entity);
        }
    }

    /**
     * Register indexer required data inside event object
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event) {
        /* @var $object Mana_Db_Model_Entity */
        $object = $event->getData('data_object');
        $key = $event->getData('entity') . '-' . $object->getId();
        if (isset($this->_matchedEvents[$key])) {
            $event
                ->addNewData('entity_filters', $this->_matchedEvents[$key]->getData('entity_filters'))
                ->addNewData('entity_filter_id', $object->getId());
        }
    }

    /**
     * Process event based on event state data
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event) {
        $this->process($event->getNewData());
    }

    /**
     * @return Mana_Db_Resource_Entity_Indexer
     */
    protected function _getResource() {
        if (!$this->_resourceName) {
            $this->_resourceName = (string)$this->getXml()->resource;
        }
        if (!$this->_resourceName) {
            $this->_resourceName = 'mana_db/entity_indexer';
        }

        return Mage::getResourceSingleton($this->_resourceName);
    }

    public function reindexAll() {
        $this->process(array(
            "{$this->getCode()}_reindex_all" => true
        ));

        return $this;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function _prepareOptions($options) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        $result = array();
        foreach ($options as $key => $value) {
            if ($core->startsWith($key, $this->getCode() . '_')) {
                $key = substr($key, strlen($this->getCode() . '_'));
            }
            $result[$key] = $value;
        }

        $result = array_merge(array(
            'reindex_all' => false,
        ), $result);

        return $result;
    }

    /**
     * @throws Exception
     * @return Varien_Simplexml_Element[]
     */
    protected function _sortTargetsByDependency() {
        $targets = array();

        if ($this->getXml()->targets) {
            foreach ($this->getXml()->targets->children() as $target) {
                $targets[] = $target;
            }
        }

        if ($count = count($targets)) {
            $orders = array();
            for ($position = 0; $position < $count; $position++) {
                $found = false;
                foreach ($targets as $target) {
                    if (!isset($orders[$target->getName()])) {
                        $hasUnresolvedDependency = false;
                        if (isset($target->depends)) {
                            foreach ($target->depends->children() as $dependency) {
                                if (!isset($orders[$dependency->getName()])) {
                                    // $dependency not yet sorted so $module should wait until that happens
                                    $hasUnresolvedDependency = true;
                                    break;
                                }
                            }
                        }
                        if (!$hasUnresolvedDependency) {
                            $found = $target;
                            break;
                        }
                    }
                }
                if ($found) {
                    $found->sort_order = $orders[$found->getName()] = count($orders);
                }
                else {
                    $circular = array();
                    foreach ($targets as $target) {
                        if (!isset($orders[$target->getName()])) {
                            $circular[] = $target->getName();
                        }
                    }
                    throw new Exception(sprintf(
                        "Circular target dependency is not allowed. " .
                            "Please check these targets: %s.",
                        implode(', ', $circular)
                    ));
                }
            }
            uasort($targets, array($this, '_compareBySortOrder'));
        }

        return $targets;
    }

    protected function _compareBySortOrder($a, $b) {
        if (((int)(string)$a->sort_order) < ((int)(string)$b->sort_order)) return -1;
        if (((int)(string)$a->sort_order) > ((int)(string)$b->sort_order)) return 1;

        return 0;
    }

    /**
     * @param Varien_Simplexml_Element $target
     * @param array $options
     */
    protected function _processTarget($target, $options) {
        $entity = (string)$target->entity;

        /* @var $globalScope Varien_Simplexml_Element */
        $globalScope = null;
        /* @var $storeScope Varien_Simplexml_Element */
        $storeScope = null;

        foreach ($this->dbConfigHelper()->getEntityXml($entity)->scopes->children() as $scope) {
            if (isset($scope->flattens)) {
                $flattenedScope = (string)$scope->flattens;
                if (isset($this->dbConfigHelper()->getScopeXml($flattenedScope)->store_specifics_for)) {
                    $storeScope = $scope;
                }
                else {
                    $globalScope = $scope;
                }
            }
        }

        if ($globalScope) {
            $this->_flattenGlobalScope($target, $globalScope, $options);
        }
        if ($storeScope) {
            $this->_flattenStoreScope($target, $storeScope, $options);
        }
    }

    /**
     * @param Varien_Simplexml_Element $target
     * @param Varien_Simplexml_Element $scope
     * @param array $options
     */
    protected function _flattenGlobalScope($target, $scope, $options) {
        $this->_getResource()->flattenScope($this, $target, $scope, $options);
    }

    /**
     * @param Varien_Simplexml_Element $target
     * @param Varien_Simplexml_Element $scope
     * @param array $options
     */
    protected function _flattenStoreScope($target, $scope, $options) {
        $this->_getResource()->flattenScope($this, $target, $scope, $options);
    }

    /**
     * @param array $options
     */
    public function process($options = array()) {
        $options = $this->_prepareOptions($options);
        $targets = $this->_sortTargetsByDependency();
        foreach ($targets as $target) {
            $this->_processTarget($target, $options);
        }
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription() {
        $targets = $this->_sortTargetsByDependency();
        $descriptions = array();
        foreach ($targets as $target) {
            if (isset($target->description)) {
                $descriptions[] = (string)$target->description;
            }
        }

        $result = parent::getDescription();
        if (count($descriptions)) {
            $result .= ': '.implode(', ', $descriptions);
        }
        return $result;
    }

    #region Dependencies
    /**
     * @return Mana_Db_Helper_Config
     */
    public function dbConfigHelper() {
        return Mage::helper('mana_db/config');
    }

    /**
     * @return Mana_Db_Helper_Formula_Processor_Table
     */
    public function tableProcessorHelper() {
        return Mage::helper('mana_db/formula_processor_table');
    }
    #endregion
}