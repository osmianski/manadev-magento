<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterTree
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterTree_Model_ParentsChildren extends Mana_Filters_Model_Filter_Category {
    protected $_currentCategory;
    protected $_parentCategories;
    protected $_parentCategoryItems;
    protected $_currentCategoryItem;

    protected function _getAllCategories() {
        if (!$this->_countedCategories) {
            $category = $this->isApplied() ? $this->getAppliedCategory() : $this->getCategory();
            $this->_countedCategories = $this->getChildrenCollection($category, self::GET_ALL_DIRECT_CHILDREN);
            if (!count($this->_countedCategories) &&
                Mage::getStoreConfigFlag('mana_filters/parents_children/show_siblings_of_deepest_subcategory'))
            {
                $category = $category->getParentCategory();
                $this->_countedCategories = $this->getChildrenCollection($category, self::GET_ALL_DIRECT_CHILDREN);
            }
            $parents = $this->getChildrenCollection($category, self::GET_ALL_PARENTS);
            $this->_parentCategories = array();
            $parentCategories = array();
            foreach ($parents as $parent) {
                /* @var $parent Mage_Catalog_Model_Category */
                if ($parent->getId() == $category->getId()) {
                    $this->_currentCategory = $parent;
                }
                else {
                    $parentCategories[$parent->getId()] = $parent;
                }
            }
            foreach ($category->getPathIds() as $id) {
                if (isset($parentCategories[$id])) {
                    $this->_parentCategories[] = $parentCategories[$id];
                }
            }
        }
    }

    public function getItemsCount() {
        $result = parent::getItemsCount();
        if ($result == 0 && !Mage::getStoreConfigFlag('mana_filters/parents_children/show_siblings_of_deepest_subcategory')) {
            $result = 1;
        }

        return $result;
    }

    public function getCountedCategories() {
        $this->_getAllCategories();
        return $this->_countedCategories;
    }

    public function getCurrentCategory() {
        $this->_getAllCategories();
        return $this->_currentCategory;
    }

    public function getParentCategories() {
        $this->_getAllCategories();
        return $this->_parentCategories;
    }

    public function getParentCategoryItems() {
        if (!$this->_parentCategoryItems) {
            $this->_parentCategoryItems = array();
            foreach ($this->getParentCategories() as $category) {
                /* @var $category Mage_Catalog_Model_Category */

                $this->_parentCategoryItems[] = new Varien_Object(array(
                    'label' => $category->getName(),
                    'replace_url' => $this->getParentCategoryUrl($category),
                ));
            }
        }

        return $this->_parentCategoryItems;
    }

    public function getCurrentCategoryItem() {
        if (!$this->_currentCategoryItem) {
            if ($category = $this->getCurrentCategory()) {
                $this->_currentCategoryItem = new Varien_Object(array(
                    'label' => $category->getName(),
                    'replace_url' => $this->getParentCategoryUrl($category),
                    'm_selected' => ($this->_currentCategory->getId() == ($this->isApplied() ? $this->getAppliedCategory()->getId() : $this->getCategory()->getId())),
                ));
            }
        }

        return $this->_currentCategoryItem;

    }

    public function getParentCategoryUrl($category) {
        /* @var $category Mage_Catalog_Model_Category */

        $pageCategoryId = $this->getCategory()->getId();

        if ($pageCategoryId == $category->getId()) {
            $query = array('cat' => null);
        }
        else/*if (in_array($pageCategoryId, $category->getPathIds()))*/ {
            $query = array('cat' => $category->getId());
        }
        $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_m_escape'] = '';
        $params['_query'] = $query;

        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }

    public function countOnCollection($collection) {
        if ($category = $this->getCurrentCategory()) {
            $collection->addCategoryFilter($category);
        }
        $this->addCountToCategories($this->getCountedCategories(), $collection);

        return $this->getCountedCategories();
    }

}