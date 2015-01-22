<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterTree
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_FilterTree module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_FilterTree_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_rootCategory;
    protected $_firstLevelCategories = array();

    /**
     * @return Mage_Catalog_Model_Category
     */
    public function getRootCategory() {
        if (!$this->_rootCategory) {
            $this->_rootCategory = Mage::getModel('catalog/category')->load(
                Mage::app()->getStore()->getRootCategoryId());
        }
        return $this->_rootCategory;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Model_Category
     */
    public function getFirstLevelCategory($category) {
        if (!isset($this->_firstLevelCategories[$category->getId()])) {
            $rootId = Mage::app()->getStore()->getRootCategoryId();
            $id = $rootId;
            $rootFound = false;
            foreach ($category->getPathIds() as $pathId) {
                if ($rootFound) {
                    $id = $pathId;
                    break;
                }
                else {
                    if ($pathId == $rootId) {
                        $rootFound = true;
                    }
                }
            }

            if ($id == $category->getId()) {
                $this->_firstLevelCategories[$category->getId()] = $category;
            }
            elseif ($id == $rootId) {
                $this->_firstLevelCategories[$category->getId()] = $this->getRootCategory();
            }
            else {
                $this->_firstLevelCategories[$category->getId()] = Mage::getModel('catalog/category')
                    ->setStoreId(Mage::app()->getStore()->getId())->load($id);
            }
        }

        return $this->_firstLevelCategories[$category->getId()];
    }
}