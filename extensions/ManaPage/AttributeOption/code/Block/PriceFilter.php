<?php
/**
 * @category    Mana
 * @package     ManaPage_AttributeOption
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 */
class ManaPage_AttributeOption_Block_PriceFilter extends Mana_Page_Block_Filter {
    public function prepareProductCollection() {
        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $this->_productCollection->getConnection();

        $select = $this->_productCollection->getSelect();

        $table = 'price_index';
        $rate = $this->getCurrencyRate();
        $priceExpr = new Zend_Db_Expr("(({$table}.min_price) * {$rate})");

        $condition = array();
        foreach ($this->_filter as $filter) {
            list($operator, $value) = $filter;

            $condition[] = $db->quoteInto("$priceExpr $operator ?", $value);
        }

        $this->_condition = strtolower($this->getOperation()) == 'or'
            ? implode(' OR ', $condition)
            : implode(' AND ', $condition);

        return $this;
    }

    protected $_filter = array();
    public function addFilter($value, $operator) {
        $this->_filter[] = array($operator, $value);
    }

    public function getCurrencyRate()
    {
        $rate = $this->_getData('currency_rate');
        if (is_null($rate)) {
            $rate = Mage::app()->getStore($this->getStoreId())->getCurrentCurrencyRate();
        }
        if (!$rate) {
            $rate = 1;
        }
        return $rate;
    }
}