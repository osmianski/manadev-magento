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
     * @return Varien_Db_Select
     */
    public function select($entity, $formulas) {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $resource Mana_Db_Resource_Formula */
        $resource = Mage::getResourceSingleton('mana_db/formula');

        /* @var $selector Mana_Db_Helper_Formula_Selector */
        $selector = Mage::helper('mana_db/formula_selector');

        /* @var $context Mana_Db_Model_Formula_Context */
        $context = Mage::getModel('mana_db/formula_context');
        $context
            ->setAlias('primary')
            ->setEntity($entity)
            ->setProcessor('entity')
            ->setHelper($selector);

        $select = $context->getSelect()
            ->from(array($context->registerAlias('primary') => $resource->getTable($dbHelper->getScopedName($entity))), null);

        // process formulas
        foreach ($this->_getFieldFormulas($context, $formulas) as $field) {
            if (!isset($field->role)) {
                $context->setField($field);
                if ($field->hasFormula()) {
                    $selector->selectFormula($context, $field->getFormula());
                }
                elseif ($field->hasValue()) {
                    $selector->selectValue($context, $field->getValue());
                }
                else {
                    $selector->selectDefaultValue($context);
                }
            }
        }

        return $select;
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


    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string[] $formulas
     * @return Mana_Db_Model_Formula_Field[]
     */
    protected function _getFieldFormulas($context, $formulas) {
        $result = array();
        $entity = $context->getEntity();

        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        /* @var $fieldsXml Varien_Simplexml_Element */
        /** @noinspection PhpUndefinedFieldInspection */
        $fieldsXml = $dbConfig->getScopeXml($entity)->fields;
        foreach ($fieldsXml->children() as $name => $fieldXml) {
            /* @var $field Mana_Db_Model_Formula_Field */
            $field = Mage::getModel('mana_db/formula_field');
            $field
                ->setName($name)
                ->setType((string)$fieldXml->type);

            if (isset($fieldXml->no)) {
                $field->setNo((string)$fieldXml->no);
                if (isset($formulas[$name])) {
                    $field
                        ->setFormula($this->parse($formulas[$name]))
                        ->setDependencies($this->depends($context, $field->getFormula()));
                }
                elseif(isset($fieldXml->default_formula)) {
                    $field
                        ->setFormula($this->parse((string)$fieldXml->default_formula))
                        ->setDependencies($this->depends($context, $field->getFormula()));
                }
                elseif (isset($fieldXml->default_value)) {
                    $field->setValue((string)$fieldXml->default_value);
                }
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

}