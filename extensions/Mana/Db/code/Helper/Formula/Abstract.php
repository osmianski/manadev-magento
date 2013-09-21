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
class Mana_Db_Helper_Formula_Abstract extends Mage_Core_Helper_Abstract {
    const CLASS_PREFIX = 'Mana_Db_Model_Formula_Node_';
    protected $_methodPrefix = '';

    /**
     * @var Mana_Db_Model_Formula_Expr
     */
    protected $_exprPrototype;

    public function __construct() {
        $this->_exprPrototype = Mage::getModel('mana_db/formula_expr');
    }

    /**
     * @return Mana_Db_Model_Formula_Expr
     */
    public function expr() {
        return clone $this->_exprPrototype;
    }

    /**
     * @param Mana_Db_Model_Formula_Expr $expr
     * @param string $type
     * @return Mana_Db_Model_Formula_Expr
     */
    public function cast($expr, $type) {
        return $expr;
    }

    public function _call($args) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');
        $formula = $args[1];
        $class = get_class($formula);
        if ($core->startsWith($class, self::CLASS_PREFIX)) {
            return call_user_func_array(array($this, $this->_methodPrefix . substr($class, strlen(self::CLASS_PREFIX))), $args);
        }
        else {
            throw new $this->__("Formula node class must start with '%s'", self::CLASS_PREFIX);
        }
    }

    public function __call($name, $args) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        if ($core->startsWith($name, $this->_methodPrefix)) {
            return call_user_func_array(array($this, $this->_methodPrefix . 'Node'), $args);
        }
        else {
            throw new $this->__("Unknown method %s::%s", __CLASS__, $name);
        }
    }

    /**
     * @param string $name
     * @return Mana_Db_Helper_Formula_Function
     */
    public function getFunction($name) {
        return Mage::helper('mana_db/formula_function_' . lcfirst(uc_words(strtolower($name), '')));
    }
}