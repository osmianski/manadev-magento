<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Block_ExcludeProductsNotAssignedToSubCategories extends Mage_Core_Block_Template {
    protected function _prepareLayout()
    {
        /* @var $layoutHelper Mana_Core_Helper_Layout */
        $layoutHelper = Mage::helper('mana_core/layout');
        $layoutHelper->delayPrepareLayout($this, 100);

        return $this;
    }

    public function delayedPrepareLayout() {
        $layer = $this->getLayer();
        if ($this->getShowRootCategory()) {
            $this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
        }

        if (Mage::registry('product')) {
            $categories = Mage::registry('product')->getCategoryCollection()
                    ->setPage(1, 1)
                    ->load();
            if ($categories->count()) {
                $this->setCategoryId(current($categories->getIterator()));
            }
        }

        $origCategory = null;
        if ($this->getCategoryId()) {
            $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
            if ($category->getId()) {
                $origCategory = $layer->getCurrentCategory();
                $layer->setCurrentCategory($category);
            }
            $categoryId = $this->getCategoryId();
        }
        else {
            $categoryId = Mage::app()->getStore()->getRootCategoryId();
        }

        $this->_prepareProductCollection($layer->getProductCollection(), $categoryId);

        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        return parent::_prepareLayout();
    }

    public function getLayer() {
        return Mage::helper('mana_core/layer')->getLayer();
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @param $categoryId
     */
    protected function _prepareProductCollection($collection, $categoryId) {
        $res = $collection->getResource();
        $db = $res->getReadConnection();
        $storeId = Mage::app()->getStore()->getId();
        $subSelect = $db->select()
            ->from(array('subcat_index' => $res->getTable('catalog/category_product_index')),
                new Zend_Db_Expr("`subcat_index`.`product_id`"))
            ->joinInner(array('subcat' => $res->getTable('catalog/category')),
                "`subcat`.`entity_id` = `subcat_index`.`category_id`", null)
            ->where("`subcat_index`.`store_id`=$storeId AND `subcat_index`.`visibility` IN(2, 4) AND `subcat`.`parent_id` = ?",
                $categoryId);

        $collection->getSelect()->where("`e`.`entity_id` IN ({$subSelect})");
    }
}