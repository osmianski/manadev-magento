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
class Mana_Db_Helper_Formula_Function_If extends Mana_Db_Helper_Formula_Function {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Expr[] $args
     * @throws Mana_Db_Exception_Formula
     * @throws Exception
     * @return Mana_Db_Model_Formula_Expr
     */
    public function select($context, $args) {
        if (count($args) != 3) {
            throw new Mana_Db_Exception_Formula($this->__("Function '%s' expects three parameters", $this->getName()));
        }
        if ($args[0]->getIsAggregate()) {
            throw new Mana_Db_Exception_Formula($this->__("You can only use aggregate function on aggregate fields"));
        }

        /* @var $helper Mana_Db_Helper_Formula_Selector */
        $helper = $context->getHelper();

        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        $condition = $args[0]->getExpr();
        switch ($formulaHelper->getType($args[0]->getType())) {
            case 'string':
                $condition .= "<> ''";
                break;
            case 'int':
                $condition .= "<> 0";
                break;
            case 'bool':
                break;
            default:
                throw new Exception('Not implemented');
        }

        $helper->binaryCast($args[1], $args[2]);
        return $helper->expr()->setExpr("IF({$condition}, {$args[1]->getExpr()}, {$args[2]->getExpr()})")->setType($args[1]->getType());
    }
}