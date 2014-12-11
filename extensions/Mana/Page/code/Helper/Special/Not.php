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
class Mana_Page_Helper_Special_Not extends Mana_Page_Helper_Special_Operator {
    protected $_operator = "OR";

    public function where($xml) {
        return "NOT " . parent::where($xml);
    }
}