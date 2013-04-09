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
class Mana_Db_Helper_Formula_DependencyFinder extends Mana_Db_Helper_Formula_Abstract {
    protected $_methodPrefix = '_findIn';
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node $formula
     * @return string[]
     */
    public function findInFormula(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        return $this->_call(func_get_args());
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Add $formula
     * @return string[]
     */
    protected function _findInAdd($context, $formula) {
        return array_merge($this->findInFormula($context, $formula->a), $this->findInFormula($context, $formula->b));
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Field $formula
     * @return string[]
     */
    protected function _findInField(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        if (count($formula->identifiers) == 2 && $formula->identifiers[0] == 'this') {
            return array($formula->identifiers[1]);
        }
        else {
            return array();
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_FormulaExpr $formula
     * @return string[]
     */
    protected function _findInFormulaExpr($context, $formula) {
        $result = array();
        foreach ($formula->parts as $part) {
            $result = array_merge($result, $this->findInFormula($context, $part));
        }
        return $result;
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_FunctionCall $formula
     * @return string[]
     */
    protected function _findInFunctionCall($context, $formula) {
        $result = array();
        foreach ($formula->args as $arg) {
            $result = array_merge($result, $this->findInFormula($context, $arg));
        }

        return $result;
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Identifier $formula
     * @return string[]
     */
    protected function _findInIdentifier(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        return array($formula->identifier);
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Multiply $formula
     * @return string[]
     */
    protected function _findInMultiply($context, $formula) {
        return array_merge($this->findInFormula($context, $formula->a), $this->findInFormula($context, $formula->b));
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node $formula
     * @return string[]
     */
    protected function _findInNode(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        return array();
    }

}