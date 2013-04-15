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

        $sep = $helper->cast($args[1], 'varchar (255)')->getExpr();
        $lastSep = count($args) == 3 ? $helper->cast($args[2], 'varchar (255)')->getExpr() : '';
        if ($args[0]->getSubSelect()) {
            $field = $helper->cast($args[0], 'varchar (255)')->getFieldExpr();
            if (count($args) == 3) {
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
        else {
            $expr = '';
            $fieldExpr = $args[0]->getFieldExpr();
            foreach ($fieldExpr as $index => $field) {
                $nextField = isset($fieldExpr[$index + 1]) ? $fieldExpr[$index + 1] : '';
                if ($expr) {
                    if (count($args) == 3) {
                        if ($nextField) {
                            $expr .= ", IF ($field IS NULL, '', CONCAT(IF ($nextField IS NULL, $lastSep, $sep), $field))";
                        }
                        else {
                            $expr .= ", IF ($field IS NULL, '', CONCAT($lastSep, $field))";
                        }
                    }
                    else {
                        $expr .= ", IF ($field IS NULL, '', CONCAT($sep, $field))";
                    }
                }
                else {
                    $expr .= "COALESCE({$helper->cast($args[0]->setExpr($field), 'varchar (255)')->getExpr()}, '')";
                }
            }
            $expr = "CONCAT($expr)";

            return $helper->expr()->setExpr($expr)->setType('varchar(255)');
        }
    }
}