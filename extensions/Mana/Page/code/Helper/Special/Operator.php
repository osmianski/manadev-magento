<?php
/** 
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Page_Helper_Special_Operator extends Mana_Page_Helper_Special_Rule {
    protected $_operator;

    public function join($select, $xml) {
        foreach ($xml->children() as $childXml) {
            $rule = $this->specialPageHelper()->rule($childXml);
            $rule->join($select, $childXml);
        }
    }

    public function where($xml) {
        $condition = "";
        foreach ($xml->children() as $childXml) {
            $rule = $this->specialPageHelper()->rule($childXml);
            if ($condition) {
                $condition .= " {$this->_operator} ";
            }
            $condition .= "(" . $rule->where($childXml) . ")";
        }

        return $condition;
    }
}