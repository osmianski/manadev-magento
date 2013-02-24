<?php
/**
 * @category    Mana
 * @package     ManaPage_Bestseller
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPage_AttributeOption_Block_Widget extends Mana_Page_Block_Widget
{
    protected $_filters = array();
    public function addFilter($attributeCode, $value, $operator = 'eq') {
        $this->_filters[] = compact('attributeCode', 'operator', 'value');
    }

    protected $_attributes;
    protected function _getAttributeCodes() {
        $result = array();
        foreach ($this->_filters as $options) {
            $result[$options['attributeCode']] = $options['attributeCode'];
        }
        return array_keys($result);
    }

    protected function _getAttributes() {
        if (!$this->_attributes) {
            /* @var $attributes Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection */
            $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
            $attributes
                ->addFieldToFilter('attribute_code', array('in', $this->_getAttributeCodes()))
                ->setItemObjectClass('catalog/resource_eav_attribute');
            $attributes->getSelect()->distinct(true);

            $this->_attributes = $attributes;
        }
        return $this->_attributes;
    }
    /**
     * @param $code
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected function _getAttribute($code) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));
        $collection = $this->_getAttributes();
        $attribute = $core->collectionFind($collection, 'attribute_code', $code);

        return $attribute;
    }
    protected function _translateOptions($attributeCode, $options) {
        $attribute = $this->_getAttribute($attributeCode);
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');
        $db = $res->getConnection('read');

        /* @var $select Varien_Db_Select */
        $select = $db->select();
        if ($attribute->getData('backend_model') == 'eav/entity_attribute_backend_array' || $attribute->getData('source_model') == 'eav/entity_attribute_source_table' || $attribute->getData('frontend_input') == 'select' && !$attribute->getData('source_model')) {
            $select
                ->from(array('o' => $res->getTableName('eav_attribute_option')), 'option_id')
                ->join(array('v' => $res->getTableName('eav_attribute_option_value')), 'o.option_id = v.option_id', null)
                ->where('LOWER(v.value) = ?', strtolower($options['value']))
                ->where('o.attribute_id = ?', $attribute->getId())
                ->where('v.store_id IN (?)', array(0, $this->getStoreId()));
            $options['value'] = $db->fetchOne($select);

            if ($attribute->getIsFilterable() >= 0) {
                // does not work well for non-simple products
                //$options['useAttributeIndex'] = true;
            }
            elseif ($attribute->getData('backend_model') == 'eav/entity_attribute_backend_array') {
                $options['operator'] = 'finset'; //'FIND_IN_SET({{value}}, {{field}})';
            }
        }
        return $options;
    }

    protected function _prepareFilters() {
        $this->addFilter($this->getAttributeCode(), $this->getAttributeValue());
        return $this;
    }

    protected function _prepareCollection($collection)
    {
        $this->_prepareFilters();
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');
        $db = $res->getConnection('read');

        foreach ($this->_filters as $options) {
            $attributeCode = $options['attributeCode'];
            unset($options['attributeCode']);
            $attribute = $this->_getAttribute($attributeCode);
            $options = $this->_translateOptions($attributeCode, $options);
            if (!empty($options['useAttributeIndex'])) {
                $tableAlias = $attributeCode . '_mpc_idx';
                $conditions = array(
                    "{$tableAlias}.entity_id = e.entity_id",
                    $db->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                    $db->quoteInto("{$tableAlias}.store_id = ?", $this->getStoreId()),
                    "{$tableAlias}.value in (" . implode(',', array_filter(explode('_', $options['value']))) . ")"
                );
                $collection->getSelect()
                        ->distinct()
                        ->join(
                    array($tableAlias => $res->getTableName('catalog/product_index_eav')),
                    join(' AND ', $conditions),
                    array()
                );
            }
            else {
                $collection->addAttributeToFilter($attributeCode, array($options['operator'] => $options['value']));
            }
        }
        return $this;
    }

    public function getType() {
        return 'attribute-option';
    }
}