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
class Mana_Db_Helper_Formula extends Mage_Core_Helper_Abstract {
    protected $_orders;
    static $_types = array(
        'varchar' => 'string',
        'mediumtext' => 'string',
        'text' => 'string',
        'tinyint' => 'int',
        'smallint' => 'int',
        'int' => 'int',
        'bigint' => 'int',
    );
    static $_numericTypes = array(
        8 => 'tinyint',
        16 => 'smallint',
        32 => 'int',
    );

    protected $_typeCache = array();

    /**
     * @param string $entity
     * @param string[] $formulas
     * @param array $options
     * @throws Exception|Mana_Db_Exception_Formula
     * @return Mana_Db_Model_Formula_Context
     */
    public function select($entity, $formulas, $options = array()) {
        $options = array_merge(array(
            'process_all_fields' => true,
        ), $options);

        /* @var $select Varien_Db_Select */
        /* @var $selector Mana_Db_Helper_Formula_Selector */
        /* @var $context Mana_Db_Model_Formula_Context */
        $this->_initSelect($entity, $options, $select, $context, $selector);

        // process formulas
        foreach ($this->_getFieldFormulas($context, $formulas, $options) as $field) {
            $context->setField($field);
            try {
                if ($field->getRole()) {
                    if ($field->hasFormula()) {
                        $selector->selectFormula($context, $field->getFormula());
                        $context->addField($field->getName());
                    }
                    else {
                        if ($selector->selectSystemField($context)) {
                            $context->addField($field->getName());
                        }
                    }
                }
                elseif ($field->hasFormula()) {
                    $selector->selectFormula($context, $field->getFormula());
                    $context->addField($field->getName());
                }
                elseif ($field->hasValue()) {
                    $selector->selectValue($context, $field->getValue());
                    $context->addField($field->getName());
                }
                else {
                    $selector->selectDefaultValue($context);
                    $context->addField($field->getName());
                }
            }
            catch (Mana_Db_Exception_Formula $e) {
                if ($context->getOption('provide_field_details_in_exceptions')) {
                    if ($field->hasFormula()) {
                        $e->addMessage($this->__(" in entity: '%s', field: '%s', formula '%s'", $entity, $field->getName(), $field->getFormulaString()));
                    }
                    else {
                        $e->addMessage($this->__(" in entity: '%s', field: '%s'", $entity, $field->getName()));
                    }
                }
                throw $e;
            }
        }

        return $context;
    }

    /**
     * @param Mana_Db_Model_Entity $model
     * @return mixed
     */
    public function evaluate($model) {
        /* @var $evaluator Mana_Db_Helper_Formula_Evaluator */
        $evaluator = Mage::helper('mana_db/formula_evaluator');

        /* @var $context Mana_Db_Model_Formula_Context */
        $context = Mage::getModel('mana_db/formula_context');
        $context
            ->setEntity($model->getScope())
            ->setModel($model)
            ->setProcessor('entity')
            ->setHelper($evaluator);

        if ($defaultFormulas = $model->getDefaultFormulas()) {
            $defaultFormulas = json_decode($defaultFormulas, true);
        }
        else {
            $defaultFormulas = array();
        }

        foreach ($this->_getFieldFormulas($context, $defaultFormulas) as $field) {
            if ($field->useDefault($model)) {
                if ($field->hasFormula()) {
                    $evaluator->evaluateFormula($context, $field);
                }
                elseif ($field->hasValue()) {
                    $evaluator->evaluateValue($context, $field);
                }
            }
        }

        return $context->getResult();
    }

    /**
     * @param $formula
     * @return Mana_Db_Model_Formula_Node
     */
    public function parse($formula) {
        /* @var $parser Mana_Db_Helper_Formula_Parser */
        $parser = Mage::helper('mana_db/formula_parser');

        return $parser->parse($formula);
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node $formula
     * @return string[]
     */
    public function depends($context, $formula) {
        /* @var $dependencyFinder Mana_Db_Helper_Formula_DependencyFinder */
        $dependencyFinder = Mage::helper('mana_db/formula_dependencyFinder');
        return $dependencyFinder->findInFormula($context, $formula);
    }


    public function getFormulaGroups($entity, $options = array()) {
        /* @var $select Varien_Db_Select */
        /* @var $selector Mana_Db_Helper_Formula_Selector */
        /* @var $context Mana_Db_Model_Formula_Context */
        $this->_initSelect($entity, $options, $select, $context, $selector);
        $idExpr = "`{$context->registerAlias('primary')}`.`id`";
        $hashExpr = "`{$context->registerAlias('primary')}`.`default_formula_hash`";
        $formulasExpr = "`{$context->registerAlias('primary')}`.`default_formulas`";


        $select
            ->distinct()
            ->columns(array('id' => new Zend_Db_Expr("MIN($idExpr)")))
            ->columns(array('hash' => new Zend_Db_Expr("$hashExpr")))
            ->group('hash');

        /* @var $resource Mana_Db_Resource_Formula */
        $resource = Mage::getResourceSingleton('mana_db/formula');
        $db = $resource->getReadConnection();
        $hashes = $db->fetchPairs($select);
        if (($nullId = array_search(null, $hashes)) !== false) {
            unset($hashes[$nullId]);
        }

        if (count($hashes)) {
            $this->_initSelect($entity, $options, $select, $context, $selector);
            $select
                ->columns(array('id' => new Zend_Db_Expr("$idExpr")))
                ->columns(array('formulas' => new Zend_Db_Expr("$formulasExpr")))
                ->where("$idExpr IN (?)", array_keys($hashes));

            $formulas = $db->fetchPairs($select);

            $result = array_combine($hashes, $formulas);
        }
        else {
            $result = array();
        }
        if ($nullId !== false) {
            $result[''] = '';
        }
        return $result;
    }

    /**
     * @param string $entity
     * @param string $targetEntity
     * @param array $options
     * @throws Exception
     * @return string
     */
    public function delete($entity, $targetEntity, $options = array()) {
        throw new Exception('Not implemented');
        /* @var $select Varien_Db_Select */
        /* @var $selector Mana_Db_Helper_Formula_Selector */
        /* @var $context Mana_Db_Model_Formula_Context */
        $this->_initSelect($entity, $options, $select, $context, $selector);

        /* @var $resource Mana_Db_Resource_Formula */
        $resource = Mage::getResourceSingleton('mana_db/formula');

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        $select->setPart(Varien_Db_Select::FROM, array());
        return $select->deleteFromSelect($resource->getTable($dbHelper->getScopedName($targetEntity)));
    }

    /**
     * @param string $entity
     * @param array $options
     * @param Varien_Db_Select $select
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Helper_Formula_Selector $selector
     */
    protected function _initSelect($entity, $options, &$select, &$context, &$selector) {
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        $scopeXml = $dbConfig->getScopeXml($entity);

        /* @var $selector Mana_Db_Helper_Formula_Selector */
        $selector = Mage::helper('mana_db/formula_selector');

        /* @var $normalEntity Mana_Db_Helper_Formula_Entity_Normal */
        $normalEntity = Mage::helper('mana_db/formula_entity_normal');

        /* @var $context Mana_Db_Model_Formula_Context */
        $context = Mage::getModel('mana_db/formula_context');

        /** @noinspection PhpUndefinedFieldInspection */
        $context
            ->setAlias($this->createAlias(''))
            ->setEntity($entity)
            ->setPrimaryEntity((string)$scopeXml->flattens)
            ->setTargetEntity($entity)
            ->setProcessor('entity')
            ->setEntityHelper($normalEntity)
            ->setHelper($selector)
            ->setOptions($options);

        /** @noinspection PhpUndefinedFieldInspection */
        $select = $this->createSelect($context, $scopeXml->formula->base);
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string[] $formulas
     * @return Mana_Db_Model_Formula_Field[]
     */
    protected function _getFieldFormulas($context, $formulas, $options = array()) {
        $result = array();
        $entity = $context->getTargetEntity();

        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        /* @var $fieldsXml Varien_Simplexml_Element */
        /** @noinspection PhpUndefinedFieldInspection */
        $fieldsXml = $dbConfig->getScopeXml($entity)->fields;
        foreach ($fieldsXml->children() as $name => $fieldXml) {
            if (empty($options['process_all_fields']) && !isset($formulas[$name])) {
                continue;
            }
            /* @var $field Mana_Db_Model_Formula_Field */
            $field = Mage::getModel('mana_db/formula_field');
            $field
                ->setName($name)
                ->setRole(isset($fieldXml->role) ? (string)$fieldXml->role : '')
                ->setType((string)$fieldXml->type);

            if (isset($fieldXml->no)) {
                $field->setNo((string)$fieldXml->no);
            }
            if (isset($formulas[$name])) {
                $field
                    ->setFormulaString($formulas[$name])
                    ->setFormula($this->parse($formulas[$name]))
                    ->setDependencies($this->depends($context, $field->getFormula()));
            }
            elseif(isset($fieldXml->default_formula)) {
                $field
                    ->setFormulaString((string)$fieldXml->default_formula)
                    ->setFormula($this->parse((string)$fieldXml->default_formula))
                    ->setDependencies($this->depends($context, $field->getFormula()));
            }
            elseif (isset($fieldXml->default_value)) {
                $field->setValue((string)$fieldXml->default_value);
            }
            $result[$name] = $field;
        }

        $this->_sortByDependency($result);
        return $result;
    }

    /**
     * @param Mana_Db_Model_Formula_Field[] $fields
     * @throws Mana_Db_Exception_Formula
     * @return Mana_Db_Helper_Formula
     */
    protected function _sortByDependency(&$fields) {
        $count = count($fields);
        $orders = array();
        for ($position = 0; $position < $count; $position++) {
            $found = false;
            foreach ($fields as $fieldName => $field) {
                if (!isset($orders[$fieldName])) {
                    $hasUnresolvedDependency = false;
                    if ($field->hasDependencies()) {
                        foreach ($field->getDependencies() as $dependency) {
                            if (!isset($fields[$dependency])) {
                                throw new Mana_Db_Exception_Formula(Mage::helper('mana_db')->__("Field '%s' depends on undefined field '%s'", $fieldName, $dependency));
                            }
                            if (!isset($orders[$dependency])) {
                                // $dependency not yet sorted so $module should wait until that happens
                                $hasUnresolvedDependency = true;
                                break;
                            }
                        }
                    }
                    if (!$hasUnresolvedDependency) {
                        $found = $fieldName;
                        break;
                    }
                }
            }
            if ($found) {
                $orders[$found] = count($orders);
            }
            else {
                $circular = array();
                foreach ($fields as $fieldName => $field) {
                    if (!isset($orders[$fieldName])) {
                        $circular[] = $fieldName;
                    }
                }
                throw new Mana_Db_Exception_Formula(Mage::helper('mana_db')->__('Field values circularly depends on one another: %s', implode(', ', $circular)));
            }
        }
        $this->_orders = $orders;
        uasort($fields, array($this, '_sortByDependencyCallback'));

        return $this;
    }

    /**
     * @param Mana_Db_Model_Formula_Field $a
     * @param Mana_Db_Model_Formula_Field $b
     * @return int
     */
    protected function _sortByDependencyCallback($a, $b) {
        $a = $this->_orders[$a->getName()];
        $b = $this->_orders[$b->getName()];
        if ($a == $b) return 0;

        return $a < $b ? -1 : 1;
    }

    public function getType($sqlType) {
        if (!isset($this->_typeCache[$sqlType])) {
            $type = trim(strtolower($sqlType));
            $pos = false;
            if (($foundPos = strpos($type, '(')) !== false) {
                $pos = $foundPos;
            }
            if (($foundPos = strpos($type, ' ')) !== false) {
                $pos = $foundPos;
            }
            if ($pos !== false) {
                $type = substr($type, 0, $pos);
            }

            $this->_typeCache[$sqlType] = isset(self::$_types[$type]) ? self::$_types[$type] : '';
        }
        return $this->_typeCache[$sqlType];
    }

    public function getMinimumNumericType($value) {
        foreach (self::$_numericTypes as $bit => $type) {
            if (abs($value) < 1 << $bit - 1) {
                return $type;
            }
        }
        return 'bigint';
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param SimpleXMLElement $selectXml
     * @return Varien_Db_Select
     */
    public function createSelect($context, $selectXml) {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $resource Mana_Db_Resource_Formula */
        $resource = Mage::getResourceSingleton('mana_db/formula');

        $select = $context->getSelect();

        /* @var $fromXml SimpleXMLElement */
        /** @noinspection PhpUndefinedFieldInspection */
        $fromXml = $selectXml->from;
        foreach ($fromXml->children() as $alias => $definition) {
            $entity = $alias == 'primary' ? $context->getPrimaryEntity() : (string)$definition->entity;
            $select->from(
                array($context->registerAlias($alias) => $resource->getTable($dbHelper->getScopedName($entity))),
                null
            );
        }

        if (isset($selectXml->join)) {
            $joinXml = $selectXml->join;
            /* @var $joinXml SimpleXMLElement */
            foreach ($joinXml->children() as $alias => $definition) {
                $method = isset($definition->type) ? 'join' . ucfirst($definition->type) : 'joinInner';
                $entity = $alias == 'primary' ? $context->getPrimaryEntity() : (string)$definition->entity;
                $select->$method(
                    array($context->registerAlias($alias) => $resource->getTable($dbHelper->getScopedName($entity))),
                    $context->resolveAliases((string)$definition->on),
                    null
                );
            }
        }

        if (isset($selectXml->order)) {
            $select->order($context->resolveAliases((string)$selectXml->order, false));

        }
        if (isset($selectXml->where)) {
            $select->where($context->resolveAliases((string)$selectXml->where));

        }

        if ($formula = $context->getOption('entity_filter_formula')) {
            /* @var $selector Mana_Db_Helper_Formula_Selector */
            $selector = Mage::helper('mana_db/formula_selector');
            $selector->filterFormula($context, $this->parse($formula), $context->getOption('entity_filter_id'));
        }

        return $select;
    }

    /**
     * @param string | string[]  $value
     * @return Mana_Db_Model_Formula_Alias
     */
    public function createAlias($value) {
        if (!$value || $value == 'this') {
            return Mage::getModel('mana_db/formula_alias_empty');
        }
        elseif (is_array($value)) {
            return Mage::getModel('mana_db/formula_alias_array', $value);
        }
        else {
            return Mage::getModel('mana_db/formula_alias_single', $value);
        }
    }
}