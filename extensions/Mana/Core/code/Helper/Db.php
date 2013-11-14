<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_Db extends Mage_Core_Helper_Abstract {
    protected $_tableAlias;

    public function getMaskIndex($bit) {
        return ((int)floor($bit / 32));
    }

    public function getMask($bit) {
        return 1 << ($bit % 32);
    }

    public function setTableAlias($tableAlias) {
        $this->_tableAlias = $tableAlias;
        return $this;
    }
    public function isCustom($bit) {
        return "`{$this->_tableAlias}`.`default_mask{$this->getMaskIndex($bit)}` ".
            "& {$this->getMask($bit)} = {$this->getMask($bit)}";
    }

    public function wrapIntoZendDbExpr($fields) {
        $result = array();
        foreach ($fields as $key => $value) {
            $result[$key] = new Zend_Db_Expr($value);
        }
        return $result;
    }

}