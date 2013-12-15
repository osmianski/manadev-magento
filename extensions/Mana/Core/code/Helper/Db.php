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
    protected static $_seoSymbols = array(
        array('symbol' => '\\', 'substitute' => ''),
        array('symbol' => '_', 'substitute' => '-'),
        array('symbol' => '\'', 'substitute' => ''),
        array('symbol' => ':', 'substitute' => '-'),
        array('symbol' => '%', 'substitute' => ''),
        array('symbol' => '#', 'substitute' => ''),
        array('symbol' => '?', 'substitute' => ''),
        array('symbol' => '&', 'substitute' => '+'),
        array('symbol' => ' ', 'substitute' => '-'),
    );

    public function getMaskIndex($bit) {
        return ((int)floor($bit / 32));
    }

    public function getMask($bit) {
        return 1 << ($bit % 32);
    }

    /**
     * @param Varien_Object $model
     * @param int $bitNo
     * @return bool
     */
    public function isModelContainsCustomSetting($model, $bitNo) {
        $mask = $model->getData("default_mask{$this->getMaskIndex($bitNo)}");
        $bit = $this->getMask($bitNo);
        return ($mask & $bit) == $bit;
    }
    public function isCustom($tableAlias, $bit) {
        return "`{$tableAlias}`.`default_mask{$this->getMaskIndex($bit)}` ".
            "& {$this->getMask($bit)} = {$this->getMask($bit)}";
    }

    public function wrapIntoZendDbExpr($fields) {
        $result = array();
        foreach ($fields as $key => $value) {
            $result[$key] = new Zend_Db_Expr($value);
        }
        return $result;
    }

    public function seoifyExpr($expr) {
        $res = Mage::getSingleton('core/resource');
        $db = $res->getConnection('read');

        $expr = "LOWER($expr)";
        foreach ($this->getSeoSymbols() as $symbol) {
            $expr = "REPLACE($expr, {$db->quote($symbol['symbol'])}, {$db->quote($symbol['substitute'])})";
        }

        return $expr;
    }

    public function getSeoSymbols() {
        return self::$_seoSymbols;
    }
}