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
class Mana_Core_Helper_Db_Aggregate extends Mage_Core_Helper_Abstract {
    public function expr($expr, $count) {
        $result = array();
        for ($i = 0; $i < $count; $i++) {
            $result[] = str_replace('X`', "$i`", $expr);
        }
        return $result;
    }

    public function glue($exprArray, $separator, $lastSeparator = false) {
        $result = '';
        $separator = "'$separator'";
        if ($lastSeparator !== false) {
            $lastSeparator = "'$lastSeparator'";
        }
        foreach ($exprArray as $index => $expr) {
            $nextField = isset($exprArray[$index + 1]) ? $exprArray[$index + 1] : '';
            if (!$result) {
                $result = $expr;
            }
            else {
                if ($lastSeparator !== false) {
                    if ($nextField) {
                        $result .= ", IF ($expr IS NULL, '', CONCAT(IF ($nextField IS NULL, $lastSeparator, $separator), $expr))";
                    } else {
                        $result .= ", IF ($expr IS NULL, '', CONCAT($lastSeparator, $expr))";
                    }
                } else {
                    $result .= ", IF ($expr IS NULL, '', CONCAT($separator, $expr))";
                }
            }
        }
        return "CONCAT($result)";
    }

    public function wrap($pattern, $exprArray) {
        $result = array();
        foreach ($exprArray as $expr) {
            $result[] = str_replace('`X`', $expr, $pattern);
        }

        return $result;
    }

    /**
     * @param Varien_Db_Select $select
     * @param string $tableAlias
     * @param string $tableName
     * @param string $joinCondition
     * @param int $count
     * @return $this
     */
    public function joinLeft($select, $tableAlias, $tableName, $joinCondition, $count) {
        for ($i = 0; $i < $count; $i++) {
            $select->joinLeft(array(str_replace('X', $i, $tableAlias) => $tableName),
                str_replace('X`', "$i`", $joinCondition), null);
        }
        return $this;
    }
}