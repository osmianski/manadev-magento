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
class Mana_Db_Model_Entity_Indexer extends Mage_Index_Model_Indexer_Abstract {
    protected $_code;
    protected $_process;

    public function getCode() {
        return $this->_code;
    }

    /**
     * @return Mage_Index_Model_Process | bool
     */
    public function getProcess() {
        if (!$this->_process) {
            $this->_process = Mage::getModel('index/process')->load($this->getCode(), 'indexer_code');
        }

        return $this->_process;
    }

    /**
     * @return Varien_Simplexml_Element | bool
     */
    public function getXml() {
        $result = Mage::getConfig()->getXpath("//global/index/indexer/{$this->getProcess()->getIndexerCode()}");
        return count($result) == 1 ? $result[0] : false;
    }

    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName() {
        return (string)$this->getXml()->name;
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription() {
        return (string)$this->getXml()->description;
    }

    /**
     * Register indexer required data inside event object
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event) {
        // TODO: Implement _registerEvent() method.
    }

    /**
     * Process event based on event state data
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event) {
        // TODO: Implement _processEvent() method.
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

        foreach ($this->getXml()->targets->children() as $target) {
            $targets[] = $target;
        }

        $count = count($targets);
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
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        $entity = (string)$target->entity;

        /* @var $globalScope Varien_Simplexml_Element */
        $globalScope = null;
        /* @var $storeScope Varien_Simplexml_Element */
        $storeScope = null;

        foreach ($dbConfig->getEntityXml($entity)->scopes->children() as $scope) {
            if (isset($scope->flattens)) {
                $flattenedScope = (string)$scope->flattens;
                if (isset($dbConfig->getScopeXml($flattenedScope)->store_specifics_for)) {
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
        $this->_getResource()->flattenGlobalScope($this, $target, $scope, $options);
    }

    /**
     * @param Varien_Simplexml_Element $target
     * @param Varien_Simplexml_Element $scope
     * @param array $options
     */
    protected function _flattenStoreScope($target, $scope, $options) {
        $this->_getResource()->flattenStoreScope($this, $target, $scope, $options);
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
}