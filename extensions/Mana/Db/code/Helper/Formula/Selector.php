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
class Mana_Db_Helper_Formula_Selector extends Mana_Db_Helper_Formula_Abstract {
    protected $_methodPrefix = '_select';

    /**
     * @param Mana_Db_Model_Formula_TypedExpr $expr
     * @param string $type
     * @throws Mana_Db_Exception_Formula
     * @return Mana_Db_Model_Formula_TypedExpr
     */
    public function cast($expr, $type) {
        if ($expr->getIsAggregate()) {
            throw new Mana_Db_Exception_Formula($this->__("You can only use aggregate function on aggregate fields"));
        }
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        $sourceType = $formulaHelper->getType($expr->getType());
        $targetType = $formulaHelper->getType($type);
        if ($targetType == 'string') {
            if ($sourceType != 'string') {
                $expr = $this->expr()->setExpr("CONCAT({$expr->getExpr()})")->setType($type);
            }
        }
        elseif ($targetType == 'int') {
            if ($sourceType != 'int') {
                $expr = $this->expr()->setExpr("CAST({$expr->getExpr()} AS $type)")->setType($type);
            }
        }

        return $expr;
    }

    /**
     * @param Mana_Db_Model_Formula_TypedExpr $expr1
     * @param Mana_Db_Model_Formula_TypedExpr $expr2
     */
    public function binaryCast(&$expr1, &$expr2) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        if ($formulaHelper->getType($expr1->getType()) == 'string' || $formulaHelper->getType($expr2->getType()) == 'string') {
            $expr1 = $this->cast($expr1, 'varchar(255)');
            $expr2 = $this->cast($expr2, 'varchar(255)');
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node $formula
     */
    public function selectFormula(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        $typedExpr = $this->cast($this->_select($context, $formula), $formulaHelper->getType($context->getField()->getType()));
        $context->getSelect()->columns("{$typedExpr->getExpr()} AS {$context->getField()->getName()}");
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node $formula
     * @return Mana_Db_Model_Formula_TypedExpr
     */
    protected function _select(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        return $this->_call(func_get_args());
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param mixed $value
     * @throws Exception
     */
    public function selectValue($context, $value) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        if ($formulaHelper->getType($context->getField()->getType()) == 'int') {
            $context->getSelect()->columns("$value AS {$context->getField()->getName()}");
        }
        else {
            $context->getSelect()->columns("'$value' AS {$context->getField()->getName()}");
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $type
     */
    public function selectDefaultValue($context, $type) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        if ($formulaHelper->getType($context->getField()->getType()) == 'int') {
            $context->getSelect()->columns("0 AS {$context->getField()->getName()}");
        }
        else {
            $context->getSelect()->columns("''' AS {$context->getField()->getName()}");
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Add $formula
     * @throws Mana_Db_Exception_Formula
     * @throws Exception
     * @return Mana_Db_Model_Formula_TypedExpr
     */
    protected function _selectAdd($context, $formula) {
        if ($context->getIsAggregate()) {
            throw new Mana_Db_Exception_Formula($this->__("You can only use aggregate function on aggregate fields"));
        }
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        $a = $this->_select($context, $formula->a);
        $b = $this->_select($context, $formula->b);
        $this->binaryCast($a, $b);
        switch ($formula->operator) {
            case Mana_Db_Model_Formula_Node_Add::ADD:
                if ($formulaHelper->getType($a->getType()) == 'string') {
                    return $this->expr()->setExpr("CONCAT({$a->getExpr()}, {$b->getExpr()})")->setType($a->getType());
                }
                else {
                    return $this->expr()->setExpr("{$a->getExpr()} + {$b->getExpr()}")->setType($a->getType());
                }
            case Mana_Db_Model_Formula_Node_Add::SUBTRACT:
                if ($formulaHelper->getType($a->getType()) == 'string') {
                    throw new Mana_Db_Exception_Formula($this->__("'%s' operator is not supported on fields of %s type", '-', 'string'));
                }
                else {
                    return $this->expr()->setExpr("{$a->getExpr()} - {$b->getExpr()}")->setType($a->getType());
                }
            default:
                throw new Exception('Not implemented');
        }
    }

    /**
     * Converts parsed field expression $formula into SQL column expression. If needed, introduces new joins in current
     * SELECT.
     *
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Field $formula
     * @throws Mana_Db_Exception_Formula
     * @return Mana_Db_Model_Formula_TypedExpr
     *
     * Field expression looks like "a.b.c.d".
     *
     * The last identifier is field name (in case of system configuration fields field
     * name consists of 3 last identifiers "b.c.d").
     *
     * All the identifiers before field name ("a.b.c") are entity names. Each of them is either foreign entities or
     * aggregate entities.
     *
     * Foreign entity example is
     */
    protected function _selectField($context, $formula) {
        foreach ($formula->identifiers as $index => $identifier) {
            if ($result = $context->getProcessor()->selectField($context, implode('.', array_slice($formula->identifiers, $index)))) {
                return $result;
            }
            else {
                if (!$context->getIsAggregate() && ($newContext = $context->getProcessor()->selectEntity($context, $identifier))) {
                    $context = $newContext;
                }
                else {
                    throw new Mana_Db_Exception_Formula($this->__("Unknown field or entity '%s'", $identifier));
                }
            }
        }
        throw new Mana_Db_Exception_Formula(Mage::helper('mana_db')->__("'%s' is entity, but field expected", implode('.', $formula->identifiers)));
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_FormulaExpr $formula
     * @return Mana_Db_Model_Formula_TypedExpr
     */
    protected function _selectFormulaExpr($context, $formula) {
        $parts = array();
        foreach ($formula->parts as $part) {
            $parts[] = $this->cast($this->_select($context, $part), 'varchar(255)')->getExpr();
        }
        $parts = implode(', ', $parts);
        return $this->expr()->setExpr("CONCAT($parts)")->setType('varchar(255)');
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_FunctionCall $formula
     * @return Mana_Db_Model_Formula_TypedExpr
     */
    protected function _selectFunctionCall($context, $formula) {
        $args = array();
        foreach ($formula->args as $arg) {
            $args[] = $this->_select($context, $arg);
        }

        return $this->getFunction($formula->name)->select($context, $args);
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Identifier $formula
     * @throws Mana_Db_Exception_Formula
     * @return Mana_Db_Model_Formula_TypedExpr
     */
    protected function _selectIdentifier($context, $formula) {
        if ($result = $context->getProcessor()->selectField($context, $formula->identifier)) {
            return $result;
        }
        else {
            throw new Mana_Db_Exception_Formula($this->__("Can't evaluate '%s'", $formula->identifier));
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Multiply $formula
     * @throws Mana_Db_Exception_Formula
     * @throws Exception
     * @return Mana_Db_Model_Formula_TypedExpr
     */
    protected function _selectMultiply($context, $formula) {
        if ($context->getIsAggregate()) {
            throw new Mana_Db_Exception_Formula($this->__("You can only use aggregate function on aggregate fields"));
        }
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        $a = $this->_select($context, $formula->a);
        $b = $this->_select($context, $formula->b);
        $this->binaryCast($a, $b);
        switch ($formula->operator) {
            case Mana_Db_Model_Formula_Node_Multiply::MULTIPLY:
                if ($formulaHelper->getType($a->getType()) == 'string') {
                    throw new Mana_Db_Exception_Formula($this->__("'%s' operator is not supported on fields of %s type", '*', 'string'));
                }
                else {
                    return $this->expr()->setExpr("{$a->getExpr()} * {$b->getExpr()}")->setType($a->getType());
                }
            case Mana_Db_Model_Formula_Node_Multiply::DIVIDE:
                if ($formulaHelper->getType($a->getType()) == 'string') {
                    throw new Mana_Db_Exception_Formula($this->__("'%s' operator is not supported on fields of %s type", '/', 'string'));
                }
                else {
                    return $this->expr()->setExpr("{$a->getExpr()} / {$b->getExpr()}")->setType($a->getType());
                }
            default:
                throw new Exception('Not implemented');
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_NullValue $formula
     * @return Mana_Db_Model_Formula_TypedExpr
     */
    protected function _selectNullValue(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        $type = $context->getField()->getType();
        $formulaType = $formulaHelper->getType($type);
        if ($formulaType == 'int') {
            return $this->expr()->setExpr("0")->setType($type);
        }
        else {
            return $this->expr()->setExpr("''")->setType($type);
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_NumberConstant $formula
     * @return Mana_Db_Model_Formula_TypedExpr
     */
    protected function _selectNumberConstant(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        return $this->expr()->setExpr("{$formula->value}")->setType($formulaHelper->getMinimumNumericType($formula->value));
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node $formula
     * @return Mana_Db_Model_Formula_TypedExpr
     */
    protected function _selectStringConstant(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        return $this->expr()->setExpr("'{$formula->value}'")->setType('varchar(255)');
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node $formula
     */
    protected function _selectNode(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        // all future nodes ignored
    }
}