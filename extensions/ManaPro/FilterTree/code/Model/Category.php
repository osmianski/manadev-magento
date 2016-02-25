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
/** @noinspection PhpUndefinedClassInspection */
class ManaPro_FilterTree_Model_Category extends Mana_Filters_Model_Filter_Category {
    public function getCountedCategories() {
        if (!$this->_countedCategories) {
            $category = /*$this->isApplied() ? $this->getAppliedCategory() :*/ $this->getCategory();
            $this->_countedCategories = $this->getChildrenCollection($category, self::GET_ALL_CHILDREN_RECURSIVELY);
        }
        return $this->_countedCategories;
    }

    public function getCategory() {
        if ($this->coreHelper()->getRoutePath() == 'catalog/category/view' &&
            $this->coreHelper()->isManadevSeoLayeredNavigationInstalled())
        {
            if (($schema = $this->seoHelper()->getActiveSchema(Mage::app()->getStore()->getId())) &&
                $schema->getRedirectToSubcategory())
            {
                return $this->treeHelper()->getRootCategory();
            }
            else {
                return $this->getCurrentCategory();
            }
        }
        return $this->treeHelper()->getRootCategory();
    }

    public function getCurrentCategory() {
        return parent::getCategory();
    }

    public function countOnCollection($collection) {
        $collection->addCategoryFilter($this->getCategory());
        $this->addCountToCategories($this->getCountedCategories(), $collection);

        return $this->getCountedCategories();
    }

    protected function _getItemsData() {
        $key = $this->getLayer()->getStateKey() . '_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            /* @var $query Mana_Filters_Model_Query */
            $query = $this->getQuery();
            $counts = $query->getFilterCounts($this->getFilterOptions()->getCode());

            $result = array();

            $category = $this->getCategory()->getData();
            $parents = array();
            foreach ($counts as $childCat) {
                $childCategory = $childCat->getData();
                $found = false;
                if ($childCategory['is_active'] &&
                    strpos($childCategory['path'], $category['path'] . '/') === 0 &&
                    ($this->filterHelper()->isFilterEnabled($this->getFilterOptions()) == 2 || $childCategory['product_count']))
                {
                    if (strpos($childCategory['path'], '/', strlen($category['path'] . '/')) === false) {
                        // $childCategory direct child of current root category
                        // add it to $result - array of current category children
                        $found = true;
                        $addTo = &$result;
                    }
                    else {
                        // $childCategory is indirect child of current category
                        $parentPath = substr($childCategory['path'], 0, strrpos($childCategory['path'], '/'));
                        if (isset($parents[$parentPath])) {
                            $found = true;
                            $addTo = &$parents[$parentPath]['items'];
                        }
                }
                }
                if ($found) {
                    $addTo[] = array(
                        'label' => Mage::helper('core')->htmlEscape($childCategory['name']),
                        'value' => $childCategory['entity_id'],
                        'count' => $childCategory['product_count'],
                        'm_selected' => $childCategory['entity_id'] == $this->getLayer()->getCurrentCategory()->getId(),
                        'items' => array(),
                        'position' => $childCategory['position'],
                    );
                    $parents[$childCategory['path']] = &$addTo[count($addTo) - 1];
                }
            }
            $this->_sortCategoryItemsDataRecursively($result);
            $data = $result;
            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $products
     * @return array
     */
    protected function _getCategoryItemsDataRecursively($category, $children) {
        $data = array();

        foreach ($children as $childCategory) {
            /* @var $childCategory Mage_Catalog_Model_Category */
            if ($childCategory['is_active'] &&
                strpos($childCategory['path'], $category['path'] . '/') === 0 &&
                strpos($childCategory['path'], '/', strlen($category['path'] . '/')) === false &&
                ($this->filterHelper()->isFilterEnabled($this->getFilterOptions()) == 2 || $childCategory['product_count']))
            {
                $data[] = array(
                    'label' => Mage::helper('core')->htmlEscape($childCategory['name']),
                    'value' => $childCategory['entity_id'],
                    'count' => $childCategory['product_count'],
                    'm_selected' => $childCategory['entity_id'] == $this->getLayer()->getCurrentCategory()->getId(),
                    'items' => $this->_getCategoryItemsDataRecursively($childCategory, $children),
                );
            }
        }
        return $data;
    }
    protected function _initItems() {
        $this->_items = $this->_initItemsRecursively($this->_getItemsData());
        if ($this->isApplied()) {
            $this->_markSelectedCategoryRecursively($this->getItems());
        }
        return $this;
    }
    protected function _initItemsRecursively($data) {
        $items = array();
        foreach ($data as $itemData) {
            $items[] = $item = $this->_createItemEx($itemData);
            $item->setItems($this->_initItemsRecursively($itemData['items']));
        }
        /* @var $ext Mana_Filters_Helper_Extended */
        $ext = Mage::helper(strtolower('Mana_Filters/Extended'));
        $items = $ext->processFilterItems($this, $items);

        return $items;
    }
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) {
        parent::apply($request, $filterBlock);

        return $this;
    }

    protected function _markSelectedCategoryRecursively($items) {
        foreach ($items as $item) {
            $item->setMSelected($item->getValue() == $this->getAppliedCategory()->getId());
            $this->_markSelectedCategoryRecursively($item->getItems());
        }
    }

    public function getResetValue() {
        return null;
    }

    protected function _orderCategoryItems($collection) {
        $collection->setOrder('path', 'ASC');
    }

    protected function _sortCategoryItemsDataRecursively(&$items) {
        if (!count($items)) {
            return;
        }

        usort($items, array($this, '_compareCategoryItemData'));
        foreach (array_keys($items) as $key) {
            $this->_sortCategoryItemsDataRecursively($items[$key]['items']);
        }
    }

    public function _compareCategoryItemData($a, $b) {
        if ($a['position'] < $b['position']) return -1;
        if ($a['position'] > $b['position']) return 1;
        return 0;
    }

    #region Dependencies

    /**
     * @return ManaPro_FilterTree_Helper_Data
     */
    public function treeHelper() {
        return Mage::helper('manapro_filtertree');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Seo_Helper_Data
     */
    public function seoHelper() {
        return Mage::helper('mana_seo');
    }

    #endregion
}