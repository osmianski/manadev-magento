<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Slider_Resource_Product_Collection extends Mana_Db_Resource_Object_Collection {
    protected function _construct() {
        $this->_init('manapro_slider/product');
    }

    public function addProductColumnsToSelect() {
        foreach (array('name') as $attributeCode) {
            /* @var $attribute Mage_Catalog_Model_Entity_Attribute */
            $attribute = Mage::getModel('catalog/entity_attribute');
            $attribute->loadByCode('catalog_product', $attributeCode);
            $alias = "t_product_{$attributeCode}";
            $this->getSelect()->joinLeft(
                array($alias => $attribute->getBackendTable()),
                "$alias.entity_id=main_table.product_id AND $alias.store_id = 0 AND $alias.attribute_id = {$attribute->getId()}",
                array("$alias.value AS product_{$attributeCode}"));
        }
        return $this;
    }

    public function getSelectedIds($sessionId) {
        $select = clone $this->getSelect();
        $select
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('main_table.product_id')
            ->where('main_table.edit_session_id = ?', $sessionId);

        return $this->getResource()->getReadConnection()->fetchCol($select);
    }
}