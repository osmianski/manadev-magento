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
class Mana_Db_Helper_Config extends Mage_Core_Helper_Abstract {
    const ROLE_PRIMARY_KEY = 'primary_key';
    const ROLE_DEFAULT_VALUE = 'default_value';
    const ROLE_STORE_SPECIFICS = 'store_specifics';
    const ROLE_GRID_EDITING = 'grid_editing';
    const ROLE_FLAT = 'flat';

    const MODULE_LEVEL = 0;
    const ENTITY_LEVEL = 1;
    const SCOPE_LEVEL = 2;
    const FIELD_LEVEL = 3;

    protected $_xml;
    protected $_scopeXml = array();
    protected $_configLevels = array(
        self::MODULE_LEVEL => 'module',
        self::ENTITY_LEVEL => 'entity',
        self::SCOPE_LEVEL => 'scope',
        self::FIELD_LEVEL => 'field'
    );

    /**
     * @param string $setupVersion
     * @return Mana_Db_Model_Setup_Abstract
     * @throws Exception
     */
    public function getSetup($setupVersion) {
//        $setupVersion = $this->getXml()->getNode("modules/{$moduleName}/scripts/v{$version}");
//        if (empty($setupVersion->installer)) {
//            throw new Exception($this->__("Setup version not defined for module '%s' upgrade script %s",
//                $moduleName, $version));
//        }

        $setupVersion = 'mana_db/setup_v' . str_replace('.', '', $setupVersion);
        if (!($setupVersion = Mage::getModel($setupVersion))) {
            throw new Exception($this->__("Setup version %s not found", $setupVersion));
        }

        return $setupVersion;
    }

    /**
     * Load XML config from m_db.xml files and caches it
     *
     * @return Varien_Simplexml_Config
     */
    public function getXml() {
        if (!$this->_xml) {
            $cachedXml = Mage::app()->loadCache('m_db_config');
            if ($cachedXml) {
                $this->_xml = new Varien_Simplexml_Config($cachedXml);
            }
            else {
                $config = new Varien_Simplexml_Config();
                $config->loadString('<?xml version="1.0"?><config></config>');
                Mage::getConfig()->loadModulesConfiguration('m_db.xml', $config);
                $this->_xml = $config;
                $this->_prepareXml();
                if (Mage::app()->useCache('config') && !defined('HHVM_VERSION')) {
                    Mage::app()->saveCache($config->getXmlString(), 'm_db_config', array(Mage_Core_Model_Config::CACHE_TAG));
                }
            }
        }

        return $this->_xml;
    }

    protected function _prepareXml() {
        $this->iterate(array(
            'module' => array($this, '_prepareModuleXml'),
            'entity' => array($this, '_prepareEntityXml'),
            'scope' => array($this, '_prepareScopeXml'),
            'field' => array($this, '_prepareFieldXml'),
        ));

        $this->iterate(array('module' => array($this, '_prepareCustom')));

        $this->iterate(array(
            'module' => array($this, '_prepareModuleXml'),
            'entity' => array($this, '_prepareEntityXml'),
            'scope' => array($this, '_prepareScopeXml'),
            'field' => array($this, '_prepareFieldXml'),
        ));
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     */
    protected function _prepareModuleXml($context, $module) {
        $name = $module->getName();
        if (!isset($module['module'])) {
            $module['module'] = $name;
        }
        $this->propagateName($module);
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     * @param Varien_Simplexml_Element $entity
     */
    protected function _prepareEntityXml($context, $module, $entity) {
        $this->propagateAttributes($module, $entity, array('module', 'version'));
        if (empty($entity->scopes)) {
            $entity->scopes->global->name = 'global';
        }
        $this->propagateName($entity);
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     * @param Varien_Simplexml_Element $entity
     * @param Varien_Simplexml_Element $scope
     */
    protected function _prepareScopeXml($context, $module, $entity, $scope) {
        $this->propagateAttributes($entity, $scope, array('module', 'version'));
        foreach ($entity->children() as $child) {
            if ($child->getName() != 'scopes') {
                $scope->extendChild($child);
            }
        }
        if (!empty($scope->unique)) {
            /** @noinspection PhpParamsInspection */
            $this->propagateAttributes($scope, $scope->unique, array('module', 'version'));
            foreach ($scope->unique->children() as $index) {
                /** @noinspection PhpParamsInspection */
                $this->propagateAttributes($scope->unique, $index, array('module', 'version'));
                foreach ($index->children() as $column) {
                    $this->propagateAttributes($index, $column, array('module', 'version'));
                }
            }
        }
        $this->propagateName($scope);
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     * @param Varien_Simplexml_Element $entity
     * @param Varien_Simplexml_Element $scope
     * @param Varien_Simplexml_Element $field
     */
    protected function _prepareFieldXml($context, $module, $entity, $scope, $field) {
        $this->propagateAttributes($scope, $field, array('module', 'version'));
        $this->propagateName($field);
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     */
    protected function _prepareCustom($context, $module) {
        if (!$module->installer_versions) {
            return;
        }

        foreach ($module->installer_versions->children() as $moduleVersion => $version) {
            $version = (string) $version;

            $setupVersion = $this->getSetup((string)$version);
            $setupVersion
                ->setModuleName((string)$module->name)
                ->setVersion(substr($moduleVersion, 1))
                ->prepare();
        }
    }
    /**
     * @param Varien_Simplexml_Element $source
     * @param Varien_Simplexml_Element $target
     * @param string[] $attributes
     */
    public function propagateAttributes($source, $target, $attributes) {
        foreach ($attributes as $attribute) {
            if (!isset($target[$attribute]) && isset($source[$attribute])) {
                $target[$attribute] = (string)$source[$attribute];
            }
        }
    }

    /**
     * @param Varien_Simplexml_Element $target
     */
    public function propagateName($target) {
        $target->name = $target->getName();
    }

    /**
     * @param callable[] $callbacks
     */
    public function iterate($callbacks) {
        $args = array(new Varien_Object());
        $config = $this->_xml ? $this->_xml->getNode() : $this->getXml()->getNode();
        $this->_iterateLevel(self::MODULE_LEVEL, $config->modules, $callbacks, $args,
            array($this, '_iterateEntities'));
        return $args[0];
    }

    protected function _iterateLevel ($level, $elements, $callbacks, $args, $deeperLevelIterator = null) {
        if (!$elements) {
            return;
        }
        $hasCallbacks = false;
        foreach ($this->_configLevels as $levelIndex =>$levelKey) {
            if ($levelIndex < $level) {
                continue;
            }
            $intersection = array_intersect(array("{$levelKey}_before", $levelKey, "{$levelKey}_after"),
                array_keys($callbacks));
            if (!empty($intersection)) {
                $hasCallbacks = true;
                break;
            }
        }
        if (!$hasCallbacks) {
            return;
        }

        $callbackKey = $this->_configLevels[$level];
        foreach ($elements->children() as $config) {
            $argsToBePassed = array_merge($args, array($config));
            if (isset($callbacks["{$callbackKey}_before"])) {
                call_user_func_array($callbacks["{$callbackKey}_before"], $argsToBePassed);
            }
            if (isset($callbacks[$callbackKey])) {
                call_user_func_array($callbacks[$callbackKey], $argsToBePassed);
            }
            if ($deeperLevelIterator) {
                call_user_func($deeperLevelIterator, $config, $callbacks, $argsToBePassed);
            }
            if (isset($callbacks["{$callbackKey}_after"])) {
                call_user_func_array($callbacks["{$callbackKey}_after"], $argsToBePassed);
            }
        }
    }
    protected function _iterateEntities($config, $callbacks, $args) {
        $this->_iterateLevel(self::ENTITY_LEVEL, $config->entities, $callbacks, $args,
            array($this, '_iterateScopes'));
    }
    protected function _iterateScopes($config, $callbacks, $args) {
        $this->_iterateLevel(self::SCOPE_LEVEL, $config->scopes, $callbacks, $args,
            array($this, '_iterateFields'));
    }
    protected function _iterateFields($config, $callbacks, $args) {
        $this->_iterateLevel(self::FIELD_LEVEL, $config->fields, $callbacks, $args);
    }

    /**
     * @param string $entityName
     * @return Varien_Simplexml_Element | bool
     */
    public function getEntityXml($entityName) {
        $xml = $this->getXml();
        $parts = explode('/', $entityName);
        list($module, $entity) = $parts;
        $entityXml = $xml->getXpath("//modules/$module/entities/$entity");

        return empty($entityXml) ? false : $entityXml[0];
    }

    public function getTableXml($entityName) {
        $xml = $this->getXml();
        $parts = explode('/', $entityName);
        list($module, $entity) = $parts;
        $entityXml = $xml->getXpath("//modules/$module/tables/$entity");

        return empty($entityXml) ? false : $entityXml[0];
    }


    /**
     * @param string $fullEntityName
     * @return Varien_Simplexml_Element | bool
     */
    public function getScopeXml($fullEntityName) {
        if (!isset($this->_scopeXml[$fullEntityName])) {
            $xml = $this->getXml();
            $parts = explode('/', $fullEntityName);
            if (count($parts) > 2) {
                list($module, $entity, $scope) = $parts;
            }
            else {
                list($module, $entity) = $parts;
                $scope = 'global';
            }
            $scopeXml = $xml->getXpath("//modules/$module/entities/$entity/scopes/$scope");
            if (empty($scopeXml) && $scope == 'global') {
                $scopeXml = $xml->getXpath("//modules/$module/entities/$entity");
            }

            $this->_scopeXml[$fullEntityName] = empty($scopeXml) ? false : $scopeXml[0];
        }
        return $this->_scopeXml[$fullEntityName];
    }

    /**
     * @param string $entity
     * @param string $field
     * @return Varien_Simplexml_Element | bool
     */
    public function getFieldXml($entity, $field) {
        $scopeXml = $this->getScopeXml($entity);

        $resultXml = $scopeXml->xpath("fields/$field");
        return empty($resultXml) ? false : $resultXml[0];
    }

    public function getForeignKey($parentEntity, $childEntity) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        $scopeXml = $this->getScopeXml($childEntity);

        $resultXml = $scopeXml->xpath("fields/*[foreign/entity='$parentEntity' or ".
            "foreign/entity='{$parentEntity}/global']");
        if (empty($resultXml)) {
            return false;
        }

        /* @var $fieldXml Varien_Simplexml_Element */
        $fieldXml = $resultXml[0];
        return $fieldXml->getName();
    }

    /**
     * @param string $entity
     * @return Varien_Simplexml_Element[]
     */
    public function getForeignXmls($entity) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        $scopeXml = $this->getScopeXml($entity);

        return $scopeXml->xpath("fields/*[foreign]");
    }

    /**
     * @param string $entity
     * @return Varien_Simplexml_Element[]
     */
    public function getScopeValidators($entity) {
        $scopeXml = $this->getScopeXml($entity);
        return $scopeXml->xpath("validation/*");
    }

    /**
     * @param string $entity
     * @return Varien_Simplexml_Element[]
     */
    public function getScopeFields($entity) {
        $scopeXml = $this->getScopeXml($entity);

        return $scopeXml->xpath("fields/*");
    }

    /**
     * @param string $entity
     * @param string $field
     * @return Varien_Simplexml_Element || bool
     */
    public function getScopeField($entity, $field) {
        $scopeXml = $this->getScopeXml($entity);

        /** @noinspection PhpUndefinedFieldInspection */
        $result = $scopeXml->fields->$field;
        return empty($result) ? false : $result;
    }

    /**
     * @param string $entity
     * @param string $field
     * @return Varien_Simplexml_Element[]
     */
    public function getFieldValidators($entity, $field = null) {
        $fieldXml = $entity instanceof Varien_Simplexml_Element ? $entity : $this->getFieldXml($entity, $field);

        return $fieldXml->xpath("validation/*");
    }

    /**
     * @param string $entity
     * @return Varien_Simplexml_Element[]
     */
    public function getScopePostValidators($entity) {
        $scopeXml = $this->getScopeXml($entity);

        return $scopeXml->xpath("post_validation/*");
    }

    /**
     * @param string $entity
     * @param string $field
     * @return Varien_Simplexml_Element[]
     */
    public function getFieldPostValidators($entity, $field = null) {
        $fieldXml = $entity instanceof Varien_Simplexml_Element ? $entity : $this->getFieldXml($entity, $field);

        return $fieldXml->xpath("post_validation/*");
    }

}