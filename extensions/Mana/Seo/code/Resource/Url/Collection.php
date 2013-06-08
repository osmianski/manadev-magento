<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Resource_Url_Collection extends Mana_Db_Resource_Entity_Collection {
    public function addOptionAttributeIdAndCodeToSelect() {
        $this->getSelect()
            ->joinLeft(array('o' => $this->getTable('eav/attribute_option')),
                "`o`.`option_id` = `main_table`.`option_id`",
                array('option_attribute_id' => new Zend_Db_Expr('`o`.`attribute_id`')))
            ->joinLeft(array('oa' => $this->getTable('eav/attribute')),
                "`oa`.`attribute_id` = `o`.`attribute_id`",
                array('option_attribute_code' => new Zend_Db_Expr('`oa`.`attribute_code`')));
        return $this;
    }

    public function addAttributeCodeToSelect() {
        $this->getSelect()
            ->joinLeft(array('a' => $this->getTable('eav/attribute')),
                "`a`.`attribute_id` = `main_table`.`attribute_id`",
                array('attribute_code' => new Zend_Db_Expr('`a`.`attribute_code`')));

        return $this;
    }

    public function addParentCategoryFilter($categoryId) {
        $this->getSelect()
            ->joinInner(array('c' => $this->getTable('catalog/category')),
                $this->getConnection()->quoteInto("`c`.`entity_id` = `main_table`.`category_id` AND `c`.`parent_id` = ?", $categoryId),
                null);

        return $this;
    }

    public function addOptionAttributeFilter($attributeId) {
        $this->getSelect()
            ->where('`o`.`attribute_id` = ?', $attributeId);

        return $this;
    }

    public function addManadevFilterTypeToSelect($storeId) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        if ($seo->isManadevLayeredNavigationInstalled()) {
            $this->getSelect()
                ->joinLeft(array('mfg' => $this->getTable('mana_filters/filter2')),
                    "`mfg`.`code` = `a`.`attribute_code`", null)
                ->joinLeft(array('mfs' => $this->getTable('mana_filters/filter2_store')),
                    $this->getConnection()->quoteInto("`mfs`.`global_id` = `mfg`.`id` AND `mfs`.`store_id` = ?", $storeId),
                    array('filter_display' => new Zend_Db_Expr('`mfs`.`display`')));
        }
        return $this;
    }

    /**
     * @param Mana_Seo_Model_Schema $schema
     * @return Mana_Seo_Resource_Url_Collection
     */
    public function setSchemaFilter($schema) {
        $this->getSelect()->where("`main_table`.`schema_id` = ?", $schema->getId());

        return $this;
    }

}