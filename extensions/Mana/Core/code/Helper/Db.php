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

    public function getModelFieldBitNo($model, $field) {
        return @constant(get_class($model).'::DM_'.strtoupper($field));
    }

    /**
     * @param Varien_Object $model
     * @param int|string $bitNo
     * @param null $value
     * @return bool
     */
    public function isModelContainsCustomSetting($model, $bitNo, $value = null) {
        if (is_string($bitNo)) {
            $bitNo = $this->getModelFieldBitNo($model, $bitNo);
            if (is_null($bitNo)) {
                return is_null($value) ? true : $value;
            }
        }
        $maskField = "default_mask{$this->getMaskIndex($bitNo)}";
        $mask = $model->getData($maskField);
        if (is_null($mask)) {
            $mask = 0;
        }
        $bit = $this->getMask($bitNo);
        if (is_null($value)) {
            return ($mask & $bit) == $bit;
        }
        else {
            $model->setData($maskField, $value ? $mask | $bit : $mask & ~$bit);
            return $value;
        }
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

    public function makeNotEmpty($expr) {
        return "IF ($expr = '', '-', $expr)";
    }

    public function getSeoSymbols() {
        return self::$_seoSymbols;
    }

    public function scheduleReindexing($code) {
        if ($reindex = Mage::registry('m_reindex')) {
            Mage::unregister('m_reindex');
        } else {
            $reindex = array();
        }
        $reindex[$code] = $code;
        Mage::register('m_reindex', $reindex);

        return $this;
    }

    public function indexRequiresReindexing($code) {
        /* @var $indexer Mage_Index_Model_Indexer */
        $indexer = Mage::getSingleton('index/indexer');
        $process = $indexer->getProcessByCode($code);
        return $process->getData('status') != Mage_Index_Model_Process::STATUS_PENDING;
    }
}