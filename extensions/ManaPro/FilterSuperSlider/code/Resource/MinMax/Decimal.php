<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterSuperSlider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterSuperSlider_Resource_MinMax_Decimal extends ManaPro_FilterSuperSlider_Resource_Decimal {
    protected function _getApplyCondition($model, $range) {
        /* @var $model ManaPro_FilterSuperSlider_Model_MinMax_Decimal */
        $min = $model;
        $max = $model->getMaxFilter();
        $minAlias = $min->getAttributeModel()->getAttributeCode() . '_idx';
        $maxAlias = $max->getAttributeModel()->getAttributeCode() . '_idx';

        return '((' . "GREATEST({$minAlias}.value, {$maxAlias}.value)" . ' >= ' . $range['from'] . ') ' .
            'AND (' . "LEAST({$minAlias}.value, {$maxAlias}.value)" . ($this->isUpperBoundInclusive() ? ' <= ' : ' < ') . $range['to'] . '))';
    }

    protected function _applyJoin($model, $collection) {
        /* @var $model ManaPro_FilterSuperSlider_Model_MinMax_Decimal */
        parent::_applyJoin($model, $collection);
        parent::_applyJoin($model->getMaxFilter(), $collection);
    }
}