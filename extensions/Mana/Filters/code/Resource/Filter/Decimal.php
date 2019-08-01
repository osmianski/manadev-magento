<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Resource type which contains sql code for applying filters and related operations
 * @author Mana Team
 * Injected instead of standard resource catalog/layer_filter_attribute in 
 * Mana_Filters_Model_Filter_Price::_getResource().
 */
class Mana_Filters_Resource_Filter_Decimal extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Decimal {
    protected $_preparedExpressions = array();

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param Mana_Filters_Model_Filter_Attribute $model
     * @return Mana_Filters_Resource_Filter_Decimal
     */
    public function countOnCollection($collection, $model) {
        $select = $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $attributeId = $model->getAttributeModel()->getId();
        $storeId     = $collection->getStoreId();

        $select->join(
            array('decimal_index' => $this->getMainTable()),
            'e.entity_id = decimal_index.entity_id'.
            ' AND ' . $this->_getReadAdapter()->quoteInto('decimal_index.attribute_id = ?', $attributeId) .
            ' AND ' . $this->_getReadAdapter()->quoteInto('decimal_index.store_id = ?', $storeId),
            array()
        );

        $adapter = $this->_getReadAdapter();

        $expr = $this->_getDecimalExpression($model, $select, 'decimal_index');
        $countExpr = new Zend_Db_Expr("COUNT(DISTINCT e.entity_id)");
        $rangeExpr = new Zend_Db_Expr("FLOOR(($expr) / {$model->getRange()}) + 1");

        $select->columns(array('range' => $rangeExpr, 'count' => $countExpr));
        $select->group('range');
        //$sql = $select->__toString();

        return $adapter->fetchPairs($select);
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param Mana_Filters_Model_Filter_Decimal $model
     * @param array $value
     * @return Mana_Filters_Resource_Filter_Decimal
     */
    public function applyToCollection($collection, $model, $value) {
        $condition = '';
        foreach ($value as $selection) {
            if (strpos($selection, ',') !== false) {
                list($index, $range) = explode(',', $selection);
                $range = $this->getRange($index, $range);
                if ($condition != '') $condition .= ' OR ';
                $condition .= $this->_getApplyCondition($model, $range, $collection->getSelect());
            }
        }

        if ($condition) {
            $this->_applyJoin($model, $collection);
            $collection->getSelect()
                ->distinct()
                ->where($condition);
        }

        return $this;
    }

    protected function _getApplyCondition($model, $range, $select) {
        $tableAlias = $model->getAttributeModel()->getAttributeCode() . '_idx';

        $expr = $this->_getDecimalExpression($model, $select, $tableAlias);
        return '((' . $expr . ' >= ' . $range['from'] . ') ' .
            'AND (' . $expr . ($this->isUpperBoundInclusive() ? ' <= ' : ' < ') . $range['to'] . '))';

    }

    protected function _applyJoin($model, $collection) {
        $attribute  = $model->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId())
        );

        $collection->getSelect()->join(
            array($tableAlias => $this->getMainTable()),
            join(' AND ', $conditions),
            array()
        );
    }

    public function isUpperBoundInclusive() {
        return false;
    }

    protected function _getSelectForCollection($filter, $collection)
    {
        // clone select from collection with filters
        $select = clone $collection->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $attributeId = $filter->getAttributeModel()->getId();
        $storeId     = $collection->getStoreId();

        $select->join(
            array('decimal_index' => $this->getMainTable()),
            'e.entity_id = decimal_index.entity_id'.
            ' AND ' . $this->_getReadAdapter()->quoteInto('decimal_index.attribute_id = ?', $attributeId) .
            ' AND ' . $this->_getReadAdapter()->quoteInto('decimal_index.store_id = ?', $storeId),
            array()
        );

        return $select;
    }

    /**
     * Retrieve maximal price for attribute
     *
     * @param Mana_Filters_Model_Filter_Decimal $filter
     * @param $collection
     * @return array
     */
    public function getMinMaxForCollection($filter, $collection)
    {
        $select     = $this->_getSelectForCollection($filter, $collection);
        $connection = $this->_getReadAdapter();

        $expr = $this->_getDecimalExpression($filter, $select, 'decimal_index');
        $select->columns(array(
            'min_value' => new Zend_Db_Expr("MIN($expr)"),
            'max_value' => new Zend_Db_Expr("MAX($expr)"),
        ));
        $this->helper()->resetProductCollectionWhereClause($select);

        $result     = $connection->fetchRow($select);
        return array($result['min_value'], $result['max_value']);
    }

    /**
     * @param Mana_Filters_Model_Filter_Decimal $filter
     * @param Varien_Db_Select $select
     * @param string $tableAlias
     * @return string
     */
    protected function _getDecimalExpression($filter, $select, $tableAlias) {
        $key = spl_object_hash($filter) . '|' . spl_object_hash($select) . '|' . $tableAlias;

        if (!isset($this->_preparedExpressions[$key])) {
            $this->_preparedExpressions[$key] = $this->_getDecimalAdditionalExpression($filter, $select, $tableAlias);
        }

        if (!$this->_preparedExpressions[$key]) {
            return "{$tableAlias}.value";
        }

        return "({$tableAlias}.value {$this->_preparedExpressions[$key]}) * {$filter->getCurrencyRate()}";
    }

    /**
     * @param Mana_Filters_Model_Filter_Decimal $filter
     * @param Varien_Db_Select $select
     * @param string $tableAlias
     * @return string
     */
    protected function _getDecimalAdditionalExpression($filter, $select, $tableAlias) {
        if (!$this->_isPriceFormat($filter)) {
            return '';
        }

        $response = new Varien_Object();
        $response->setData('additional_calculations', array());

        // prepare event arguments
        $eventArgs = array(
            'select' => $select,
            'table' => 'price_index',
            'store_id' => $filter->getStoreId(),
            'response_object' => $response,
        );

        Mage::dispatchEvent('catalog_prepare_price_select', $eventArgs);

        $result = implode('', $response->getData('additional_calculations'));
        return $this->_replacePriceExpr($result, $tableAlias);
    }

    /**
     * @param Mana_Filters_Model_Filter_Decimal $filter
     * @return bool
     */
    protected function _isPriceFormat($filter) {
        /* @var Mana_Filters_Model_Filter2_Store $filter_options */
        $filterOptions = $filter->getData('filter_options');

        $threshold = $filterOptions->getData('slider_threshold');
        if ($threshold !== null && $threshold !== '0') {
            return false;
        }

        $format = $filterOptions->getData('slider_number_format');
        if ($format !== null && $format !== '$0') {
            return false;
        }

        return true;
    }

    protected function _replacePriceExpr($expr, $tableAlias) {
        return preg_replace('/`?price_index`?\.`?min_price`?/', "{$tableAlias}.value", $expr);
    }

    public function getRange($index, $range) {
    	return array('from' => $range * ($index - 1), 'to' => $range * $index);
    }

    /**
     * @return Mana_Filters_Helper_Data|Mage_Core_Helper_Abstract
     */
    protected function helper() {
        return Mage::helper('mana_filters');
    }
}