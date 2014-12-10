<?php
/**
 * @category    Mana
 * @package     Mana)Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 */
class Mana_Page_Block_CategoryFilter extends Mana_Page_Block_Filter {
    public function prepareProductCollection() {
        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $this->_productCollection->getConnection();

        $condition = array();
        foreach ($this->_excludedCategories as $categoryId) {
            $alias = 'exc_cat_' . $categoryId;
            $select = $db->select()
                ->from(array($alias => $this->_productCollection->getTable('catalog/category_product_index')), 'product_id')
                ->where("`$alias`.`category_id` = ?", $categoryId)
                ->where("`$alias`.`store_id` = ?", Mage::app()->getStore()->getId())
                ->where("`$alias`.`product_id` = `e`.`entity_id`");
            $condition[] = "NOT EXISTS ($select)";
        }

        $this->_condition = strtolower($this->getOperation()) == 'or'
            ? implode(' OR ', $condition)
            : implode(' AND ', $condition);

        return $this;
    }

    protected $_excludedCategories = array();
    public function exclude($categoryId) {
        $this->_excludedCategories[$categoryId] = $categoryId;
    }
}