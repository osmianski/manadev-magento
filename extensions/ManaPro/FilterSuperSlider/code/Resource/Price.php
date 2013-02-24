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
class ManaPro_FilterSuperSlider_Resource_Price extends Mana_Filters_Resource_Filter_Price  {
    public function getPriceRange($index, $range) {
    	return array('from' => $index, 'to' => $range);
    }
    public function isUpperBoundInclusive() {
        return true;
    }
    public function getExistingValues($filter) {
        $connection = $this->_getReadAdapter();
    	// clone select from collection with filters
        /* @var $select Varien_Db_Select */ $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $response   = $this->_dispatchPreparePriceEvent($filter, $select);
        $table      = $this->_getIndexTableAlias();

        $additional = join('', $response->getAdditionalCalculations());
        $rate       = $filter->getCurrencyRate();
        $select->columns(array(
            'value' => new Zend_Db_Expr("(({$table}.min_price {$additional}) * {$rate})")
        ));
        $select->reset(Zend_Db_Select::WHERE);
        $select->where("{$table}.min_price > 0");
        $select->group('value');
        $select->order('value ASC');
        return $connection->fetchCol($select);
    }
}