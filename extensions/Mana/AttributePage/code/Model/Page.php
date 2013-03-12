<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Model_Page extends Mana_Db_Model_Entity {
    public function hasAtLeastOneAttribute($dataSource) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $db->getResourceModel('mana_attributepage/page_attribute_collection');
        $collection->setEditFilter(true, 'page_id='.$this->getId());
        $sql = $collection->getSelectCountSql();
        $count = $collection->getResource()->getReadConnection()->fetchOne($sql);
        if (!$count) {
            throw new Mana_Db_Exception_Validation(Mage::helper('mana_attributepage')->__('Please add at least one attribute to the page.'));
        }
    }

    public function hasNonRepeatingAttributes($dataSource) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $db->getResourceModel('mana_attributepage/page_attribute_collection');
        $collection->setEditFilter(true, 'page_id=' . $this->getId());
        $collection->getSelect()
            ->columns('attribute_id');
        $columns = $collection->getResource()->getReadConnection()->fetchCol($collection->getSelect());
        if (count($columns) != count(array_unique($columns))) {
            throw new Mana_Db_Exception_Validation(Mage::helper('mana_attributepage')->__('Please remove duplicate attribute(s) from the page.'));
        }
    }

    public function hasUniqueAttributeCombination($dataSource) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $db->getResourceModel('mana_attributepage/page_attribute_collection');
        $collection->setEditFilter(true, 'page_id IS NOT NULL');
        $collection->getSelect()
            ->columns(array('page_id', 'attribute_id'))
            ->order(array('page_id ASC', 'attribute_id ASC'));

        $attributes = array();

        foreach ($collection as $attribute) {
            /* @var $attribute Mana_Db_Model_Entity */
            $pageId = $attribute->getPageId();
            $attributeId = $attribute->getAttributeId();

            if (!isset($attributes[$pageId])) {
                $attributes[$pageId] = '';
            }
            if ($attributes[$pageId]) {
                $attributes[$pageId] .= ',';
            }
            $attributes[$pageId] .= $attributeId;
        }

        if (count($attributes) != count(array_unique($attributes))) {
            throw new Mana_Db_Exception_Validation(Mage::helper('mana_attributepage')->__('Attribute pages for selected attribute(s) already exists.'));
        }
    }
}