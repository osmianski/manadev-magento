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
class Mana_Db_Helper_Formula_Entity_Normal extends Mana_Db_Helper_Formula_Entity {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Field $formula
     * @param Mana_Db_Model_Formula_Expr $expr
     */
    public function selectField(/** @noinspection PhpUnusedParameterInspection */$context, $formula, $expr) {
        foreach($context->getSelect()->getPart(Varien_Db_Select::COLUMNS) as $column) {
            if ($column[2] == $expr->getFieldName()) {
                $expr->setExpr($column[1]);
                return;
            }
        }

        throw new Mana_Db_Exception_Formula($this->__("Field '%s' referenced in field '%s' is not defined.", $expr->getFieldName(), $context->getField()->getName()));
    }

    /**
     *
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    public function select($context, $entity) {
    }
}