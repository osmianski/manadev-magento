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
     * @param Mana_Db_Model_Formula_Expr $expr
     * @param string $type
     * @throws Mana_Db_Exception_Formula
     * @return Mana_Db_Model_Formula_Expr
     */
    public function cast($expr, $type) {
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
     * @param Mana_Db_Model_Formula_Expr $expr1
     * @param Mana_Db_Model_Formula_Expr $expr2
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
        $expr = $this->_select($context, $formula);
        if ($expr->getIsAggregate()) {
            throw new Mana_Db_Exception_Formula($this->__("Aggregate field used outside of aggregate function"));
        }
        $expr = $this->cast($expr, $context->getField()->getType());
        $expr = $this->_getOverriddenValueExpr($context, $expr->getExpr());
        $context->getSelect()->columns(array($context->getField()->getName() => new Zend_Db_Expr(
            $expr)));
    }

    public function filterFormula(/** @noinspection PhpUnusedParameterInspection */$context, $formula, $id) {
        $context->getSelect()->where($this->_select($context, $formula)->getExpr() .' = ?', $id);
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node $formula
     * @return Mana_Db_Model_Formula_Expr
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
        $expr = $this->_getOverriddenValueExpr($context, $this->_getValue($context, $value));
        $context->getSelect()->columns(array($context->getField()->getName() => new Zend_Db_Expr($expr)));
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param mixed $value
     * @return string
     */
    protected function _getValue($context, $value) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        if ($formulaHelper->getType($context->getField()->getType()) == 'int') {
            return $value;
        }
        else {
            /* @var $resource Mana_Db_Resource_Formula */
            $resource = Mage::getResourceSingleton('mana_db/formula');

            return $resource->getReadConnection()->quote($value);
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param mixed $value
     * @return string
     */
    protected function _getDefaultValue($context) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        if ($formulaHelper->getType($context->getField()->getType()) == 'int') {
            return "0";
        }
        else {
            return "''";
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     */
    public function selectDefaultValue($context) {
        $expr = $this->_getOverriddenValueExpr($context, $this->_getDefaultValue($context));
        $context->getSelect()->columns(array($context->getField()->getName() => new Zend_Db_Expr($expr)));
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @return bool
     */
    public function selectSystemField($context) {
        if ($context->getField()->getRole() == Mana_Db_Helper_Config::ROLE_PRIMARY_KEY) {
            return false;
        }

        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        $fieldExpr = "`{$context->registerAlias('primary')}`.`{$context->getField()->getName()}`";
        if ($context->getField()->hasValue()) {
            $context->getSelect()->columns(array($context->getField()->getName() => new Zend_Db_Expr(
                "COALESCE($fieldExpr, {$this->_getValue($context, $context->getField()->getValue())})")));
        }
        else {
            $context->getSelect()->columns(array(
                $context->getField()->getName() => new Zend_Db_Expr(
                    "COALESCE($fieldExpr, {$this->_getDefaultValue($context)})")
            ));
        }
        return true;
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Add $formula
     * @throws Mana_Db_Exception_Formula
     * @throws Exception
     * @return Mana_Db_Model_Formula_Expr
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
     * @param Mana_Db_Model_Formula_Context $originalContext
     * @param Mana_Db_Model_Formula_Node_Field $formula
     * @throws Mana_Db_Exception_Formula
     * @return Mana_Db_Model_Formula_Expr
     *
     * Field expression looks like "a.b.c.d".
     *
     * The last identifier is field name (in case of system configuration fields field
     * name consists of 3 last identifiers "b.c.d").
     *
     * All the identifiers before field name ("a.b.c") are entity names. Each of them is either foreign entities,
     * frontend entities or aggregate entities:
     *    * foreign entity example: catalog/product for sales/order_item. For foreign entity LEFT or INNER JOIN is added based on
     *      foreign key and primary key relationship and column using that foreign key is added.
     *    * aggregate entity example sales/order_item for sales/order. Fields from aggregate entities are returned to
     *      parent formula node which is expected to be an aggregate formula such as GLUE, SUM, MAX. In final SQL aggregate
     *      fields appear as sub selects, so sub select object is returned for aggregate field as well.
     *    * frontend entity example: catalog/category for mana_attributepage/page (in database there is no relation to
     *      frontend entity, but it is known when calculated in frontend, typically from Mage::registry()). Such entities
     *      are left unprocessed (with type = 'formula,int' or 'formula,string') and are only processed before displaying
     *      data in frontend. Frontend entities can appear in identifier chain in first position only.
     *
     * How these entity types behave in identifier chain:
     *    * foreign entity:
     *        * no aggregate or frontend entity met: adds JOIN to main SELECT and changes current entity in main context
     *        * inside aggregate, no frontend entity met: adds JOIN to aggregate SUBSELECT and changes current entity in
     *          subselect context
     *        * inside frontend entity: changes current entity in main context
     *    * aggregate entity:
     *        * no frontend entity met: creates aggregate context to collect aggregate subselect
     *        * inside frontend entity: changes current entity in main context
     *    * frontend entity: sets a flag for processing frontend entity
     */
    protected function _selectField($originalContext, $formula) {
        if ($formula->identifiers[0] == 'this') {
            if (count($formula->identifiers) == 1) {
                throw new Mana_Db_Exception_Formula(Mage::helper('mana_db')->__("'%s' is entity, but field expected", implode('.', $formula->identifiers)));
            }
            $formula->identifiers = array_slice($formula->identifiers, 1);
            if (count($formula->identifiers) == 1) {
                /* @var $identifier Mana_Db_Model_Formula_Node_Identifier */
                $identifier = Mage::getModel('mana_db/formula_node_identifier');
                $identifier->identifier = $formula->identifiers[0];
                $this->_selectIdentifier($originalContext, $identifier);
            }
        }
        $context = clone $originalContext;
        $result = $this->_selectFieldRecursively($context, $formula, 0);
        $originalContext->copyAliases($context);
        return $result;
    }

    /**
     * Converts parsed field expression $formula into SQL column expression. If needed, introduces new joins in current
     * SELECT.
     *
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Field $formula
     * @param int $index
     * @throws Mana_Db_Exception_Formula
     * @return Mana_Db_Model_Formula_Expr
     */
    protected function _selectFieldRecursively($context, $formula, $index) {
        $processor = $context->getProcessor();
        if (isset($formula->identifiers[$index])) {
            $identifier = $formula->identifiers[$index];
        }
        else {
            throw new Mana_Db_Exception_Formula(Mage::helper('mana_db')->__("'%s' is entity, but field expected", implode('.', $formula->identifiers)));
        }

        if ($result = $processor->selectField($context, implode('.', array_slice($formula->identifiers, $index)))) {
            $context->getEntityHelper()->selectField($context, $formula, $result);
            return $result;
        }
        else {
            if ($entity = $processor->selectEntity($context, $identifier)) {
                $entity->getHelper()->select($context, $entity);
                $result = $this->_selectFieldRecursively($context, $formula, ++$index);
                $entity->getHelper()->endSelect($context, $entity);
                return $result;
            }
            elseif ($index == 0) {
                $context->setEntity($context->getPrimaryEntity());
                return $this->_selectFieldRecursively($context, $formula, $index);
            }
            else {
                throw new Mana_Db_Exception_Formula($this->__("Unknown field or entity '%s'", $identifier));
            }
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_FormulaExpr $formula
     * @return Mana_Db_Model_Formula_Expr
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
     * @return Mana_Db_Model_Formula_Expr
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
     * @return Mana_Db_Model_Formula_Expr
     */
    protected function _selectIdentifier($context, $formula) {
        if ($result = $context->getProcessor()->selectField($context, $formula->identifier)) {
            $context->getEntityHelper()->selectField($context, null, $result);

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
     * @return Mana_Db_Model_Formula_Expr
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
     * @return Mana_Db_Model_Formula_Expr
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
     * @return Mana_Db_Model_Formula_Expr
     */
    protected function _selectNumberConstant(/** @noinspection PhpUnusedParameterInspection */$context, $formula) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        return $this->expr()->setExpr("{$formula->value}")->setType($formulaHelper->getMinimumNumericType($formula->value));
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_StringConstant $formula
     * @return Mana_Db_Model_Formula_Expr
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

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $defaultExpr
     * @return string
     */
    protected function _getOverriddenValueExpr($context, $defaultExpr) {
        if (($no = $context->getField()->getNo()) !== null) {
            $overriddenExpr = "`p`.`{$context->getField()->getName()}`";
            if ($overriddenExpr != $defaultExpr) {
                /* @var $db Mana_Db_Helper_Data */
                $db = Mage::helper('mana_db');

                $condition = "`p`.`default_mask{$db->getMaskIndex($no)}` & {$db->getMask($no)} = {$db->getMask($no)}";
                return "IF ($condition, $overriddenExpr, $defaultExpr)";
            }
            else {
                return $overriddenExpr;
            }
        }
        else {
            return $defaultExpr;
        }
    }
}