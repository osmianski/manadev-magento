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
class Mana_Db_Helper_Formula_Function_Glue extends Mana_Db_Helper_Formula_Function {
    protected $_separator = '{~#';
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Expr[] $args
     * @throws Mana_Db_Exception_Formula
     * @return Mana_Db_Model_Formula_Expr
     */
    public function select($context, $args) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        if (count($args) < 2 || count($args) > 3) {
            throw new Mana_Db_Exception_Formula($this->__("Function '%s' expects 2 or 3 parameters", $this->getName()));
        }
        if (!$args[0]->getIsAggregate()) {
            throw new Mana_Db_Exception_Formula($this->__("Function '%s' expects 1 parameter to be a field of aggregate entity", $this->getName()));
        }

        $helper = $context->getHelper();

        $field = $helper->cast($args[0], 'varchar (255)')->getFieldExpr();
        $sep = $helper->cast($args[1], 'varchar (255)')->getExpr();
        if (count($args) == 3) {
            $lastSep = $helper->cast($args[2], 'varchar (255)')->getExpr();
            $expr =
                "IF (COUNT({$args[0]->getFieldExpr()}) > 1, ".
                    "REPLACE(CONCAT(".
                        "SUBSTRING_INDEX(GROUP_CONCAT({$field} SEPARATOR '{$this->_separator}'), ".
                            "'{$this->_separator}', COUNT({$field}) - 1), ".
                        "$lastSep, ".
                        "SUBSTRING_INDEX(GROUP_CONCAT({$field} SEPARATOR '{$this->_separator}'), ".
                            "'{$this->_separator}', -1)".
                    "), '{$this->_separator}', $sep), ".
                    "GROUP_CONCAT({$field} SEPARATOR $sep)".
               ")";
        }
        else {
            $expr = "GROUP_CONCAT({$field} SEPARATOR $sep)";
        }

        return $helper->expr()->setExpr("({$args[0]->getSubSelect()->columns($expr)})")->setType('varchar(255)');
    }
}