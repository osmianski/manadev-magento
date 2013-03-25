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
class Mana_Db_Helper_Formula_Function_SkinUrl extends Mana_Db_Helper_Formula_Function {
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
        if ($args[0]->getIsAggregate()) {
            throw new Mana_Db_Exception_Formula($this->__("You can only use aggregate function on aggregate fields"));
        }

        $helper = $context->getHelper();

        $urlConstant = str_replace('\'', '\\\'', substr($args[0]->getExpr(), 1, strlen($args[0]->getExpr())));
        return $helper->expr()->setExpr("'{{= {$this->getName()}(\\'$urlConstant\\')'")->setType('varchar(255)');
    }
}