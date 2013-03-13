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
class Mana_Db_Model_Formula_Engine  {
    /**
     * @param string[] $formulas
     */
    public function parseFormulas($formulas) {
        $result = array();
        foreach ($formulas as $key => $formula) {
            $result[$key]  = $this->parseFormula($formula);
        }

    }

    public function parseFormula($formula) {
        /* @var $parser Mana_Db_Model_Formula_Parser */
        $parser = Mage::getModel('mana_db/formula_parser');
        return $parser->parseText($formula);
    }
}