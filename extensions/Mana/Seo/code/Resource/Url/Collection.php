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
    const TYPE_PAGE = 0x01;
    const TYPE_PARAMETER = 0x02;
    const TYPE_ATTRIBUTE_VALUE = 0x04;
    const TYPE_CATEGORY_VALUE = 0x08;

    public function addParentCategoryFilter($categoryPath) {
        $this->getSelect()
            ->joinInner(array('c' => $this->getTable('catalog/category')),
                "`c`.`entity_id` = `main_table`.`category_id` AND `c`.`path` LIKE '$categoryPath/%' AND `c`.`path` NOT LIKE '$categoryPath/%/%'",
                null);

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

    public function addTypeFilter($type) {
        $conditions = array();
        if ($type & self::TYPE_PAGE) {
            $conditions[] = "(`main_table`.`is_page` = 1)";
        }
        if ($type & self::TYPE_PARAMETER) {
            $conditions[] = "(`main_table`.`is_parameter` = 1)";
        }
        if ($type & self::TYPE_ATTRIBUTE_VALUE) {
            $conditions[] = "(`main_table`.`is_attribute_value` = 1)";
        }
        if ($type & self::TYPE_CATEGORY_VALUE) {
            $conditions[] = "(`main_table`.`is_category_value` = 1)";
        }
        if (count($conditions)) {
            $this->getSelect()->where(new Zend_Db_Expr(implode(' OR ', $conditions)));
        }
        return $this;
    }

    public function addStoreAndGlobalSchemaColumns() {
        $this->getSelect()->joinInner(array('schema' => $this->getTable('mana_seo/schema_store_flat')),
            "`schema`.`id` = `main_table`.`schema_id`", array('global_id', 'store_id'));
        return $this;
    }
}