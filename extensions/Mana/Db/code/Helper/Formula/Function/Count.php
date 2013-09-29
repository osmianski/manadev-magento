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
class Mana_Db_Helper_Formula_Function_Count extends Mana_Db_Helper_Formula_Function {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Expr[] $args
     * @throws Mana_Db_Exception_Formula
     * @return Mana_Db_Model_Formula_Expr
     */
    public function select($context, $args) {
        if (count($args) != 1) {
            throw new Mana_Db_Exception_Formula($this->__("Function '%s' expects one parameter", $this->getName()));
        }
        if (!$args[0]->getIsAggregate()) {
            throw new Mana_Db_Exception_Formula($this->__("Function '%s' expects 1 parameter to be a field of aggregate entity", $this->getName()));
        }

        $helper = $context->getHelper();

        if ($args[0]->getSubSelect()) {
            $expr = "COUNT({$args[0]->getFieldExpr()})";

            return $helper->expr()->setExpr("({$args[0]->getSubSelect()->columns($expr)})")->setType('int');
        }
        else {
            $expr = '';
            $fieldExpr = $args[0]->getFieldExpr();
            foreach ($fieldExpr as $field) {
                if ($expr) {
                    $expr .= " + ";
                }
                $expr .= "IF ($field IS NULL, 0, 1)";
            }

            return $helper->expr()->setExpr($expr)->setType('int');
        }
    }
}