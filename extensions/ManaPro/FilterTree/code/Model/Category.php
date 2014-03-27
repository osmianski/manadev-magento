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
            $this->_countedCategories = $this->getChildrenCollection($category);
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
                return parent::getCategory();
            }
        }
        return $this->treeHelper()->getRootCategory();
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
            foreach ($counts as $category) {
                $result[] = $category->getData();
            }
            $data = $this->_getCategoryItemsDataRecursively($this->getCategory()->getData(), $result);
            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    public function getChildrenCollection($category) {

        /* @var $resource Mage_Catalog_Model_Resource_Eav_Mysql4_Category */
        $resource = $category->getResource();
        $categoryIds = $resource->getChildren($category);

        $collection = $category->getCollection();

        /* @var $_conn Varien_Db_Adapter_Pdo_Mysql */
        $_conn = $collection->getConnection();

        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $storeId = $category->getStoreId();
            $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns('main_table.*')
                ->joinLeft(
                    array('url_rewrite' => $collection->getTable('core/url_rewrite')),
                        'url_rewrite.category_id=main_table.entity_id AND url_rewrite.is_system=1 AND ' .
                                $_conn->quoteInto(
                                    'url_rewrite.product_id IS NULL AND url_rewrite.store_id=? AND url_rewrite.id_path LIKE "category/%"',
                                    $storeId),
                    array('request_path' => 'url_rewrite.request_path'));
        }
        else {
            $collection
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('all_children')
                ->addAttributeToSelect('is_anchor')
                ->joinUrlRewrite();
        }
        $collection
            ->addAttributeToFilter('is_active', 1)
            ->addIdFilter($categoryIds)
            ->setOrder('position', 'ASC')
            ->load();

        return $collection;
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