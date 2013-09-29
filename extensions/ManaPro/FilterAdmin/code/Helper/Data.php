<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAdmin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for ManaPro_FilterAdmin module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_FilterAdmin_Helper_Data extends Mage_Core_Helper_Abstract {
    /**
     * @param Mage_Core_Model_Store $store
     * @param bool $startingFromRoot
     */
    public function makeCategoriesAnchor($store, $startingFromRoot = true) {
        $this->_makeCategoryAnchorRecursively($store->getRootCategoryId(), $startingFromRoot);
	}

    /**
     * @param int $categoryId
     * @param bool $forceAnchor
     */
    protected function _makeCategoryAnchorRecursively($categoryId, $forceAnchor) {
        $category = $this->getCategoryModel()->setStoreId(0)->load($categoryId);
        if ($forceAnchor) {
            $category->setData('is_anchor', 1);
            $category->save();
        }
        /* @var $category Mage_Catalog_Model_Category */
        foreach ($category->getChildrenCategories() as $childCategory) {
            $this->_makeCategoryAnchorRecursively($childCategory->getId(), $category->getData('is_anchor'));
        }
    }

    #region Dependencies

    /**
     * @return Mage_Catalog_Model_Category
     */
    public function getCategoryModel() {
        return Mage::getModel('catalog/category');
    }
    #endregion
}