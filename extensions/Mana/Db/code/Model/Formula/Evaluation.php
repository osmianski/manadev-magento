<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method Mana_Db_Model_Formula_EvaluationContext getContext()
 * @method Mana_Db_Model_Formula_Evaluation setContext(Mana_Db_Model_Formula_EvaluationContext $value)
 * @method Mana_Db_Model_Formula_FieldConfig getFieldConfig()
 * @method Mana_Db_Model_Formula_Evaluation setFieldConfig(Mana_Db_Model_Formula_FieldConfig $value)
 */
class Mana_Db_Model_Formula_Evaluation extends Varien_Object {
    protected $_select;
    public function getSelect() {
        if (!$this->_select) {
            /* @var $db Mana_Db_Helper_Data */
            $db = Mage::helper('mana_db');

            /* @var $engine Mana_Db_Model_Formula_Engine */
            $engine = Mage::getSingleton('mana_db/formula_engine');

            $resource = $db->getResourceSingleton($this->getEntityConfig()->getPrimary()->getEntity());

            $select = $resource->getReadConnection()->select();
            $select->from(array('primary_table' => $this->getEntityConfig()->getPrimary()->getTableName()), null);
            foreach ($this->getFieldConfig()->getFields() as $field) {
                throw new Exception('Not implemented');
            }
            $this->_select = $select;
        }
        return $this->_select;
    }

    /**
     * @return Mana_Db_Model_Formula_Evaluation
     */
    public function evaluateModel() {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        /* @var $engine Mana_Db_Model_Formula_Engine */
        $engine = Mage::getSingleton('mana_db/formula_engine');

        $model = $this->getContext()->getModel();
        foreach ($this->getFieldConfig()->getFields() as $field) {
            if ($field->useDefault($model)) {
                $context = clone $this->getContext();
                $context->setField($field->getName());
                $model->setData($field, $this->evaluate($context->setField($field->getName()), $field->getFormula()));
            }
        }
        return $this;
    }

    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param Mana_Db_Model_Formula_Node $formula
     * @throws Exception
     * @return mixed
     */
    public function evaluate(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        return $formula->call($this, '_evaluate', func_get_args());
    }

    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param Mana_Db_Model_Formula_Node_FormulaExpr $formula
     * @return mixed
     */
    protected function _evaluateFormulaExpr($context, $formula) {
        $result = '';
        foreach ($formula->parts as $part) {
            $result .= $this->evaluate($context, $part);
        }
        return $result;
    }

    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param Mana_Db_Model_Formula_Node_NullValue $formula
     * @return mixed
     */
    protected function _evaluateNull(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        return null;
    }

    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param Mana_Db_Model_Formula_Node_StringConstant $formula
     * @return mixed
     */
    protected function _evaluateStringConstant(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        return $formula->value;
    }

    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param Mana_Db_Model_Formula_Node_Identifier $formula
     * @return mixed
     */
    protected function _evaluateIdentifier($context, $formula) {
        if ($context->getFieldResolver()->evaluate($context, $formula->identifier, $result)) {
            return $result;
        }
        else {
            throw new Mana_Db_Exception_Formula(Mage::helper('mana_db')->__("Can't evaluate '%s'", $formula->identifier));
        }
    }

    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param Mana_Db_Model_Formula_Node_NumberConstant $formula
     * @return mixed
     */
    protected function _evaluateNumberConstant(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        return $formula->value;
    }

    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param Mana_Db_Model_Formula_Node_Field $formula
     * @throws Mana_Db_Exception_Formula
     * @return mixed
     */
    protected function _evaluateField($context, $formula) {
        foreach ($formula->identifiers  as $index => $identifier) {
            if ($context->getFieldResolver()->evaluate($context, implode('.', array_slice($formula->identifiers, $index)), $result)) {
                return $result;
            }
            elseif ($resolvedContext = $context->getEntityResolver()->evaluate($context, $identifier)) {
                $context = $resolvedContext;
            }
            else {
                break;
            }
        }
        throw new Mana_Db_Exception_Formula(Mage::helper('mana_db')->__("Can't evaluate '%s'", implode('.', $formula->identifiers)));
    }

    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param Mana_Db_Model_Formula_Node_FunctionCall $formula
     * @return mixed
     */
    protected function _evaluateFunctionCall($context, $formula) {
        throw new Exception('Not implemented');
    }

    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param Mana_Db_Model_Formula_Node_Multiply $formula
     * @return mixed
     */
    protected function _evaluateMultiply($context, $formula) {
        throw new Exception('Not implemented');
    }

    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param Mana_Db_Model_Formula_Node_Multiply $formula
     * @return mixed
     */
    protected function _evaluateAdd($context, $formula) {
        throw new Exception('Not implemented');
    }
}