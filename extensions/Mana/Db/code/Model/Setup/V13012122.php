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
class Mana_Db_Model_Setup_V13012122 extends Mana_Db_Model_Setup_Abstract {
    protected $_toBeCreated = array();
    protected $_toBeAltered = array();

    public function prepare() {
        /* @var $configHelper Mana_Db_Helper_Config */
        $configHelper = Mage::helper('mana_db/config');

        $configHelper->iterate(array('scope' => array($this, '_createPrimaryKey')));
        $configHelper->iterate(array('scope' => array($this, '_createDefaultValueInfrastructure')));
        $configHelper->iterate(array('scope' => array($this, '_createStoreSpecificsInfrastructure')));
        $configHelper->iterate(array('scope' => array($this, '_createGridEditingInfrastructure')));
        $configHelper->iterate(array('scope' => array($this, '_createFlatInfrastructure')));

        return $this;
    }

    public function run() {
        /* @var $configHelper Mana_Db_Helper_Config */
        $configHelper = Mage::helper('mana_db/config');

        $result = $configHelper->iterate(array(
            'scope_before' => array($this, '_beginTableScript'),
            'field' => array($this, '_fieldScript'),
            'scope_after' => array($this, '_endTableScript'),
        ));

        if ($result->getSql()) {
            $this->getInstaller()->run($result->getSql());
        }
        return $this;
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     * @param Varien_Simplexml_Element $entity
     * @param Varien_Simplexml_Element $scope
     */
    public function _createPrimaryKey($context, $module, $entity, $scope) {
        if (((string)$scope['module']) != $this->getModuleName() || ((string)$scope['version']) != $this->getVersion()) {
            return;
        }
        /* @var $configHelper Mana_Db_Helper_Config */
        $configHelper = Mage::helper('mana_db/config');

        $primaryField = $scope->xpath('fields/*[primary="1"]');
        if (empty($primaryField)) {
            $scope->fields->id->type = 'bigint';
            $field = $scope->fields->id;
            $field->primary = 1;
            $field->role = Mana_Db_Helper_Config::ROLE_PRIMARY_KEY;
            $configHelper->propagateName($field);
            $configHelper->propagateAttributes($scope, $field, array('module', 'version'));
        }
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     * @param Varien_Simplexml_Element $entity
     * @param Varien_Simplexml_Element $scope
     */
    public function _createDefaultValueInfrastructure($context, $module, $entity, $scope) {
        if (((string)$scope['module']) != $this->getModuleName() || ((string)$scope['version']) != $this->getVersion()) {
            return;
        }

        /* @var $configHelper Mana_Db_Helper_Config */
        $configHelper = Mage::helper('mana_db/config');

        if (!empty($scope->max_defaultable_fields) && empty($scope->flattens)) {
            $maxFields = (int)(string)$scope->max_defaultable_fields;
            for ($i = 0; $i * 15 < $maxFields; $i++) {
                $fieldName = "default_mask{$i}";
                $scope->fields->$fieldName->type = 'int unsigned';
                $field = $scope->fields->$fieldName;
                $field->default_value = 0;
                $field->role = Mana_Db_Helper_Config::ROLE_DEFAULT_VALUE;
                $configHelper->propagateName($field);
                $configHelper->propagateAttributes($scope, $field, array('module', 'version'));
            }

            $fieldName = 'default_formula_hash';
            $scope->fields->$fieldName->type = 'varchar(40)';
            $field = $scope->fields->$fieldName;
            $field->indexed = 1;
            $field->role = Mana_Db_Helper_Config::ROLE_DEFAULT_VALUE;
            $configHelper->propagateName($field);
            $configHelper->propagateAttributes($scope, $field, array('module', 'version'));


            $fieldName = 'default_formulas';
            $scope->fields->$fieldName->type = 'mediumtext';
            $field = $scope->fields->$fieldName;
            $field->role = Mana_Db_Helper_Config::ROLE_DEFAULT_VALUE;
            $configHelper->propagateName($field);
            $configHelper->propagateAttributes($scope, $field, array('module', 'version'));
        }
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     * @param Varien_Simplexml_Element $entity
     * @param Varien_Simplexml_Element $scope
     */
    public function _createStoreSpecificsInfrastructure($context, $module, $entity, $scope) {
        if (((string)$scope['module']) != $this->getModuleName() || ((string)$scope['version']) != $this->getVersion()) {
            return;
        }

        /* @var $configHelper Mana_Db_Helper_Config */
        $configHelper = Mage::helper('mana_db/config');

        if (!empty($scope->store_specifics_for)) {
            $globalScope = (string)$scope->store_specifics_for;

            $fieldName = 'global_id';
            $scope->fields->$fieldName->type = 'bigint';
            $field = $scope->fields->$fieldName;
            $field->foreign->entity = $globalScope;
            $field->foreign->field = 'id';
            $field->foreign->on_update = 'cascade';
            $field->foreign->on_delete = 'cascade';
            $field->role = Mana_Db_Helper_Config::ROLE_STORE_SPECIFICS;
            $configHelper->propagateName($field);
            $configHelper->propagateAttributes($scope, $field, array('module', 'version'));

            $fieldName = 'store_id';
            $scope->fields->$fieldName->type = 'smallint(5) unsigned';
            $field = $scope->fields->$fieldName;
            $field->foreign->entity = 'core/store';
            $field->foreign->field = 'store_id';
            $field->foreign->on_update = 'cascade';
            $field->foreign->on_delete = 'cascade';
            $field->role = Mana_Db_Helper_Config::ROLE_STORE_SPECIFICS;
            $configHelper->propagateName($field);
            $configHelper->propagateAttributes($scope, $field, array('module', 'version'));


        }
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     * @param Varien_Simplexml_Element $entity
     * @param Varien_Simplexml_Element $scope
     */
    public function _createGridEditingInfrastructure($context, $module, $entity, $scope) {
        if (((string)$scope['module']) != $this->getModuleName() || ((string)$scope['version']) != $this->getVersion()) {
            return;
        }

        /* @var $configHelper Mana_Db_Helper_Config */
        $configHelper = Mage::helper('mana_db/config');

        if (!empty($scope->editable_in_grid)) {

            $fieldName = 'edit_session_id';
            $scope->fields->$fieldName->type = 'bigint';
            $field = $scope->fields->$fieldName;
            $field->default_value = 0;
            $field->foreign->entity = 'mana_db/edit_session';
            $field->foreign->field = 'id';
            $field->foreign->on_update = 'cascade';
            $field->foreign->on_delete = 'cascade';
            $field->role = Mana_Db_Helper_Config::ROLE_GRID_EDITING;
            $configHelper->propagateName($field);
            $configHelper->propagateAttributes($scope, $field, array('module', 'version'));

            $fieldName = 'edit_status';
            $scope->fields->$fieldName->type = 'bigint';
            $field = $scope->fields->$fieldName;
            $field->default_value = 0;
            $field->indexed = 1;
            $field->role = Mana_Db_Helper_Config::ROLE_GRID_EDITING;
            $configHelper->propagateName($field);
            $configHelper->propagateAttributes($scope, $field, array('module', 'version'));

            $fieldName = 'edit_massaction';
            $scope->fields->$fieldName->type = 'tinyint';
            $field = $scope->fields->$fieldName;
            $field->default_value = 0;
            $field->indexed = 1;
            $field->role = Mana_Db_Helper_Config::ROLE_GRID_EDITING;
            $configHelper->propagateName($field);
            $configHelper->propagateAttributes($scope, $field, array('module', 'version'));

        }
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     * @param Varien_Simplexml_Element $entity
     * @param Varien_Simplexml_Element $scope
     */
    public function _createFlatInfrastructure($context, $module, $entity, $scope) {
        if (((string)$scope['module']) != $this->getModuleName() || ((string)$scope['version']) != $this->getVersion()) {
            return;
        }

        /* @var $configHelper Mana_Db_Helper_Config */
        $configHelper = Mage::helper('mana_db/config');

        if (!empty($scope->flattens)) {
            $flattenedScopeXml = $configHelper->getScopeXml((string)$scope->flattens);
            if (!empty($flattenedScopeXml->store_specifics_for)) {
                $global = (string)$flattenedScopeXml->store_specifics_for;
                $fieldName = 'global_id';
                $scope->fields->$fieldName->type = 'bigint';
                $field = $scope->fields->$fieldName;
                $field->foreign->entity = substr($global, 0, strrpos($global, '/')).'/flat';
                $field->foreign->field = 'id';
                $field->foreign->on_update = 'cascade';
                $field->foreign->on_delete = 'cascade';
                $field->role = Mana_Db_Helper_Config::ROLE_STORE_SPECIFICS;
                $field->default_formula = '{{= global.id }}';
                $configHelper->propagateName($field);
                $configHelper->propagateAttributes($scope, $field, array('module', 'version'));

                $fieldName = 'store_id';
                $scope->fields->$fieldName->type = 'smallint(5) unsigned';
                $field = $scope->fields->$fieldName;
                $field->foreign->entity = 'core/store';
                $field->foreign->field = 'store_id';
                $field->foreign->on_update = 'cascade';
                $field->foreign->on_delete = 'cascade';
                $field->role = Mana_Db_Helper_Config::ROLE_STORE_SPECIFICS;
                $field->default_formula = '{{= store.store_id }}';
                $configHelper->propagateName($field);
                $configHelper->propagateAttributes($scope, $field, array('module', 'version'));

                $fieldName = 'primary_global_id';
                $scope->fields->$fieldName->type = 'bigint';
                $field = $scope->fields->$fieldName;
                $field->foreign->entity = (string)$flattenedScopeXml->store_specifics_for;
                $field->foreign->field = 'id';
                $field->role = Mana_Db_Helper_Config::ROLE_FLAT;
                $field->default_formula = '{{= global.primary.id }}';
                $field->nullable = 1;
                $configHelper->propagateName($field);
                $configHelper->propagateAttributes($scope, $field, array('module', 'version'));

                $fieldName = 'primary_id';
                $scope->fields->$fieldName->type = 'bigint';
                $field = $scope->fields->$fieldName;
                $field->foreign->entity = (string)$scope->flattens;
                $field->foreign->field = 'id';
                $field->foreign->on_update = 'cascade';
                $field->foreign->on_delete = 'cascade';
                $field->role = Mana_Db_Helper_Config::ROLE_FLAT;
                $field->default_formula = '{{= primary.id }}';
                $field->nullable = 1;
                $configHelper->propagateName($field);
                $configHelper->propagateAttributes($scope, $field, array('module', 'version'));

                $scope->unique->primary->global_id = '';
                $scope->unique->primary->store_id = '';
            }
            else {
                $fieldName = 'primary_id';
                $scope->fields->$fieldName->type = 'bigint';
                $field = $scope->fields->$fieldName;
                $field->foreign->entity = (string)$scope->flattens;
                $field->foreign->field = 'id';
                $field->foreign->on_update = 'cascade';
                $field->foreign->on_delete = 'cascade';
                $field->role = Mana_Db_Helper_Config::ROLE_FLAT;
                $field->default_formula = '{{= primary.id }}';
                $field->nullable = 1;
                $field->unique = 1;
                $configHelper->propagateName($field);
                $configHelper->propagateAttributes($scope, $field, array('module', 'version'));
            }
        }
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     * @param Varien_Simplexml_Element $entity
     * @param Varien_Simplexml_Element $scope
     */
    public function _beginTableScript($context, $module, $entity, $scope) {
        $context->setFields(array());
        $context->setIndexes(array());
        $context->setConstraints(array());
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     * @param Varien_Simplexml_Element $entity
     * @param Varien_Simplexml_Element $scope
     * @param Varien_Simplexml_Element $field
     */
    public function _fieldScript($context, $module, $entity, $scope, $field) {
        if (((string)$field['module']) != $this->getModuleName() || ((string)$field['version']) != $this->getVersion()) {
            return;
        }
        $fields = $context->getFields();
        $fields[] = $field;
        $context->setFields($fields);

        if (isset($field->indexed) || isset($field->foreign) || !empty($field->primary)) {
            $indexes = $context->getIndexes();
            $indexes[] = $field;
            $context->setIndexes($indexes);
        }

        if (isset($field->foreign)) {
            $constraints = $context->getConstraints();
            $constraints[] = $field;
            $context->setConstraints($constraints);
        }
    }

    /**
     * @param Varien_Object $context
     * @param Varien_Simplexml_Element $module
     * @param Varien_Simplexml_Element $entity
     * @param Varien_Simplexml_Element $scope
     */
    public function _endTableScript($context, $module, $entity, $scope) {
        $scope = $scope;
        if (!empty($scope->unique)) {
            /* @var $resource Mage_Core_Model_Mysql4_Resource */
            $resource = Mage::getResourceSingleton('core/resource');

            $indexes = $context->getIndexes();
            foreach ($scope->unique->children() as $unique) {
                $index = (object)array('unique' => 1, 'name' => 'unique_'.$unique->getName(), 'indexed_fields' => array());
                $includeUniqueIndex = false;
                foreach ($unique->children() as $field => $def) {
                    if (((string)$def['module']) == $this->getModuleName() && ((string)$def['version']) == $this->getVersion()) {
                        $includeUniqueIndex = true;
                        $index->indexed_fields[] = $field;
                    }
                    else {
                        $installedVersion = $resource->getDbVersion(((string)$def['module']).'_setup');
                        if ($installedVersion && version_compare($installedVersion, (string)$def['version']) >= 0) {
                            $index->indexed_fields[] = $field;
                        }
                    }
                }
                if ($includeUniqueIndex) {
                    if (!(((string)$scope->unique['module']) == $this->getModuleName() && ((string)$scope->unique['version']) == $this->getVersion())) {
                        $index->rebuild = true;
                    }
                    $indexes[] = $index;
                }
            }
            $context->setIndexes($indexes);
        }

        $sql = $context->getSql();
        $context->setTable(
            $this->getTable(((string)$module->name) . '/' .
            ((string)$entity->name) . '/' .
            ((string)$scope->name)));
        if (((string)$scope['module']) != $this->getModuleName() || ((string)$scope['version']) != $this->getVersion()) {
            foreach ($context->getFields() as $field) {
                $sql .= "ALTER TABLE `{$context->getTable()}` ADD COLUMN ( ";
                $sql .= $this->_renderField($field);
                $sql .= ");\n";
            }
            foreach ($context->getIndexes() as $index) {
                if (!empty($index->rebuild)) {
                    $sql .= "ALTER TABLE `{$context->getTable()}` DROP KEY `".(string)$index->name."`";
                    $sql .= ";\n";
                }
                $sql .= "ALTER TABLE `{$context->getTable()}` ADD ";
                $sql .= $this->_renderIndex($index);
                $sql .= ";\n";
            }
        }
        else {
            if (count($context->getFields()) || count($context->getIndexes())) {
                $sql .= "DROP TABLE IF EXISTS `{$context->getTable()}`;\n";
                $sql .= "CREATE TABLE `{$context->getTable()}` ( \n";
                $sep = false;
                foreach ($context->getFields() as $field) {
                    if ($sep) $sql .= ", \n"; else $sep = true;
                    $sql .= "    ".$this->_renderField($field);
                }
                foreach ($context->getIndexes() as $index) {
                    if ($sep) $sql .= ", \n"; else $sep = true;
                    $sql .= "    " .$this->_renderIndex($index);
                }
                $sql .= "\n";
                $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';\n";
            }
        }
        foreach ($context->getConstraints() as $constraint) {
            $sql .= "ALTER TABLE `{$context->getTable()}` ADD CONSTRAINT ";
            $sql .= $this->_renderConstraint($context, $constraint);
            $sql .= ";\n";
        }

        $context->setSql($sql);
    }

    protected function _renderField($field) {
        $sql = '';
        $sql .= "`".((string)$field->name)."` ";
        $sql .= ((string)$field->type) ." ";
        if (isset($field->nullable)) {
            $sql .= "null ";
        }
        else {
            $sql .= "NOT null ";
            if (!empty($field->default_value) && strpos(strtolower((string)$field->type), 'text') === false) {
                $sql .= "DEFAULT '".((string)$field->default_value)."' ";
            }
            elseif (strpos((string)$field->type, 'varchar') !== false) {
                $sql .= "DEFAULT '' ";
            }
        }
        if (!empty($field->primary)) {
            $sql .= "AUTO_INCREMENT ";
        }
        return $sql;
    }

    protected function _renderIndex($field) {
        $sql = '';
        if (!empty($field->primary)) {
            $sql .= "PRIMARY KEY ";
        }
        elseif (!empty($field->unique)) {
            $sql .= "UNIQUE KEY `" . ((string)$field->name) . "` ";
        }
        else {
            $sql .= "KEY `" . ((string)$field->name) . "` ";
        }
        if (empty($field->indexed_fields)) {
            $sql .= "(`" . ((string)$field->name) . "`) ";
        }
        else {
            $indexedFields = array();
            foreach ($field->indexed_fields as $indexedField) {
                $indexedFields[] = "`$indexedField`";
            }
            $sql .= "(" . implode(', ', $indexedFields) . ") ";
        }

        return $sql;
    }

    protected function _renderConstraint($context, $field) {
        $sql = '';
	    $sql .= "`FK_{$context->getTable()}_". ((string)$field->name)."` ";
	    $sql .= "FOREIGN KEY (`" . ((string)$field->name). "`) ";
	    $sql .= "REFERENCES `{$this->getTable((string)$field->foreign->entity)}` (`".((string)$field->foreign->field)."`) ";
	    if (!empty($field->foreign->on_delete)) {
            $sql .= "ON DELETE " . ((string)$field->foreign->on_delete) . " ";
        }
        if (!empty($field->foreign->on_update)) {
            $sql .= "ON UPDATE ".((string)$field->foreign->on_update)." ";
        }

        return $sql;
    }
}