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
class ManaPro_FilterTree_Model_Solr_Category extends Mana_Filters_Model_Solr_Category {
    public function getCountedCategories() {
        if (!$this->_countedCategories) {
            $category = /*$this->isApplied() ? $this->getAppliedCategory() :*/ $this->getCategory();
            $this->_countedCategories = $this->getChildrenCollection($category, self::GET_ALL_CHILDREN_RECURSIVELY);
        }
        return $this->_countedCategories;
    }
    protected function _getItemsData() {
        $key = $this->getLayer()->getStateKey() . '_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            /* @var $query Mana_Filters_Model_Query */
            $query = $this->getQuery();
            $counts = $query->getFilterCounts($this->getFilterOptions()->getCode());

            $result = array();
            foreach ($counts as $category) {
                $result[] = $category->getData();
            }
            $data = $this->_getCategoryItemsDataRecursively($this->getLayer()->getCurrentCategory()->getData(), $result);
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
                    'm_selected' => false, // filled out during apply phase
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
            if ($item->getValue() == $this->getAppliedCategory()->getId())  {
                $item->setMSelected(true);
            }
            $this->_markSelectedCategoryRecursively($item->getItems());
        }
    }

    public function isFilterAppliedWhenCounting($modelToBeApplied)
    {
        return $modelToBeApplied != $this;
    }

    public function isCountedOnMainCollection()
    {
        return false;
    }

    public function getResetValue() {
        return null;
    }
}