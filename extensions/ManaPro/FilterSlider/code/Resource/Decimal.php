<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSlider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Changes interpretation of applied price filter value
 * @author Mana Team
 *
 */
class ManaPro_FilterSlider_Resource_Decimal extends Mana_Filters_Resource_Filter_Decimal {
    public function getRange($index, $range) {
    	return array('from' => $index, 'to' => $range);
    }
    protected function _isUpperBoundInclusive() {
        return true;
    }
}