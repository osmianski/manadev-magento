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
abstract class Mana_Db_Helper_Formula_Function extends Mage_Core_Helper_Abstract {
    protected $_name;
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Expr[] $args
     * @return Mana_Db_Model_Formula_Expr
     */
    abstract public function select($context, $args);

    public function getName() {
        if (!$this->_name) {
            $class = substr(get_class($this), strlen(__CLASS__ . '_'));
            $this->_name = strtoupper(preg_replace('/(.)([A-Z])/', "$1_$2", $class));
        }
        return $this->_name;
    }
}