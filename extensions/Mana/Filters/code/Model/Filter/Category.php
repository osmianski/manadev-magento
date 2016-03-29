<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Model type for holding information in memory about possible or applied category filter
 * @author Mana Team
 * Injected instead of standard catalog/layer_filter_attribute in Mana_Filters_Block_Filter_Category constructor.
 * @method Mana_Filters_Model_Filter2_Store getFilterOptions()
 */
class Mana_Filters_Model_Filter_Category
    extends Mage_Catalog_Model_Layer_Filter_Category
    implements Mana_Filters_Interface_Filter
{
    const GET_ALL_CHILDREN_RECURSIVELY = 'recursive';
    const GET_ALL_DIRECT_CHILDREN = 'direct';
    const GET_ALL_PARENTS = 'parents';
    #region Category Specific logic
    protected $_countedCategories;


    public function getCountedCategories() {
        if (!$this->_countedCategories) {
            $category = $this->isApplied() ? $this->getAppliedCategory() : $this->getCategory();
            $this->_countedCategories = $this->getChildrenCollection($category, self::GET_ALL_DIRECT_CHILDREN);
        }
        return $this->_countedCategories;
    }
    public function getAppliedCategory() {
        if (!$this->_appliedCategory) {
            $values = $this->getMSelectedValues();
            $category = $this->getCategory();
            Mage::register('current_category_filter', $category, true);

            $this->_appliedCategory = Mage::getModel('catalog/category')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($values[0]);
        }
        return $this->_appliedCategory;
    }
    public function init()
    {
    }

    /**
     * Applies filter values provided in URL to a given product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return void
     */
    protected function _applyToCollection($collection)
    {
        if ($this->_isValidCategory($this->getAppliedCategory())) {
            $collection->addCategoryFilter($this->getAppliedCategory());
        }
    }

    protected function _getCategoryItemsData($categories)
    {
        $data = array();
        foreach ($categories as $category) {
        	if ($category->getIsActive() &&
                ($this->filterHelper()->isFilterEnabled($this->getFilterOptions()) == 2 || $category->getProductCount())) {
            	$data[] = array(
                	'label' => Mage::helper('core')->htmlEscape($category->getName()),
                    'value' => $category->getId(),
                    'count' => $category->getProductCount(),
            		'm_selected' => $category->getId() == ($this->isApplied() ? $this->getAppliedCategory()->getId() : $this->getCategory()->getId())
                );
            }
        }
        return $data;
    }
    protected function _getItemsData()
    {
        $key = $this->getLayer()->getStateKey().'_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            /* @var $query Mana_Filters_Model_Query */
            $query = $this->getQuery();
            $counts = $query->getFilterCounts($this->getFilterOptions()->getCode());
            $data = $this->_getCategoryItemsData($counts);
            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection $categories
     * @return Mana_Filters_Model_Filter_Category
     */
    public function addCountToCategories($categories, $products = null, $inCurrentCategory = false) {
        if (!$products) {
            $products = $this->getLayer()->getProductCollection();
        }
        //$products->addCountToCategories($categories);
        $items = is_array($categories) ? $categories : $categories->getItems();
        if (count($items)) {
            $category = array_shift($items);
            if (!$category->hasProductCount()) {
                Mage::helper('mana_filters')->addCountToCategories($products, $categories, $inCurrentCategory);
            }
        }
        return $this;
    }

    /**
     * Applies counting query to the current collection. The result should be suitable to processCounts() method.
     * Typically, this method should return final result - option id/count pairs for option lists or
     * min/max pair for slider. However, in some cases (like not applied Solr facets) this method returns collection
     * object and later processCounts() extracts actual counts from this collections.
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return mixed
     */
    public function countOnCollection($collection)
    {
        $this->addCountToCategories($this->getCountedCategories(), $collection);
        return $this->getCountedCategories();
    }

    public function getRangeOnCollection($collection)
    {
        return array();
    }

    /**
     * Adds all selected items of this filters to the layered navigation state object
     *
     * @return void
     */
    public function addToState()
    {
        foreach ($this->getMSelectedValues() as $optionId) {
            $this->getLayer()->getState()->addFilter(
                $this->_createItemEx(
                    array(
                        'label' => $this->getAppliedCategory()->getName(),
                        'value' => $optionId,
                        'm_selected' => true,
                        'm_show_selected' => false,
                    )
                )
            );
        }
    }

    #endregion
    #region Mana_Filters_Interface_Filter methods
    /**
     * Returns whether this filter is applied
     *
     * @return bool
     */
    public function isApplied()
    {
        $appliedValues = $this->getMSelectedValues();

        return !empty($appliedValues);
    }

    /**
     * Applies filter values provided in URL to a given product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return void
     */
    public function applyToCollection($collection)
    {
        $this->_applyToCollection($collection);
    }

    /**
     * Returns true if counting should be done on main collection query and false if a separated query should be done
     * Typically it should return false; however there are some cases (like not applied Solr facets) when it should
     * return true.
     *
     * @return bool
     */
    public function isCountedOnMainCollection()
    {
        return false;
    }

    /**
     * Returns option id/count pairs for option lists or min/max pair for slider. Typically, this method just returns
     * $counts. However, in some cases (like not applied Solr facets) this method gets a collection object with Solr
     * results and extracts those results.
     *
     * @param mixed $counts
     * @return array
     */
    public function processCounts($counts)
    {
        return $counts;
    }

    /**
     * Returns whether a given filter $modelToBeApplied should be applied when this filter is being counted. Typically,
     * returns true for all filters except this one.
     *
     * @param $modelToBeApplied
     * @return mixed
     */
    public function isFilterAppliedWhenCounting($modelToBeApplied)
    {
        return $modelToBeApplied != $this;
    }

    #endregion
    #region common part for all mana_filters/filter_* models


    /**
     * Creates in-memory representation of a single option of a filter
     * @param array $data
     * @return Mana_Filters_Model_Item
     */
    protected function _createItemEx($data)
    {
        return Mage::getModel('mana_filters/item')
                ->setData($data)
                ->setFilter($this);
    }

    /**
     * Initializes internal array of in-memory representations of options of a filter
     * @return Mana_Filters_Model_Filter_Attribute
     * @see Mage_Catalog_Model_Layer_Filter_Abstract::_initItems()
     */
    protected function _initItems()
    {
        /* @var $ext Mana_Filters_Helper_Extended */
        $ext = Mage::helper(strtolower('Mana_Filters/Extended'));

        $data = $this->_getItemsData();
        $items = array();
        foreach ($data as $itemData) {
            $items[] = $this->_createItemEx($itemData);
        }
        $items = $ext->processFilterItems($this, $items);
        $this->_items = $items;

        return $this;
    }

    /**
     * This method locates resource type which should do all dirty job with the database. In this override, we
     * instruct Magento to take our resource type, not standard.
     * @see Mage_Catalog_Model_Layer_Filter_Attribute::_getResource()
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            /* @var $helper Mana_Filters_Helper_Data */
            $helper = Mage::helper(strtolower('Mana_Filters'));

            $this->_resource = Mage::getResourceModel(
                $helper->getFilterTypeName('resource', $this->getFilterOptions())
            );
        }

        return $this->_resource;
    }

    protected function _getIsFilterable()
    {
        switch ($this->getMode()) {
            case 'category':
                return $this->getFilterOptions()->getIsEnabled();
            case 'search':
                return $this->getFilterOptions()->getIsEnabledInSearch();
            default:
                throw new Exception('Not implemented');
        }
    }

    public function getRemoveUrl()
    {
        $query = array($this->getRequestVar() => $this->getResetValue());
        if ($this->coreHelper()->isManadevDependentFilterInstalled()) {
            $query = $this->dependentHelper()->removeDependentFiltersFromUrl($query, $this->getRequestVar());
        }

        $params = array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_m_escape'] = '';
        $params['_query'] = $query;

        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }

    protected function _getIsFilterableAttribute($attribute)
    {
        return $this->_getIsFilterable(); //return $this->getFilterOptions()->getIsEnabled();
    }

    public function getName()
    {
        return $this->getFilterOptions()->getName();
    }

    /**
     * Returns all values currently selected for this filter
     */
    public function getMSelectedValues()
    {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        $values = $core->sanitizeRequestNumberParam(
            $this->_requestVar,
            array(array('sep' => '_', 'as_string' => true))
        );

        return $values ? array_filter(explode('_', $values)) : array();
    }
    #endregion

    public function getResetValue() {
        if ($this->_appliedCategory) {
            /**
             * Revert path ids
             */
            $pathIds = array_reverse($this->_appliedCategory->getPathIds());
            $curCategoryId = $this->getLayer()->getCurrentCategory()->getId();

            if ($pathIds[0] != $curCategoryId && in_array($curCategoryId, $pathIds) && isset($pathIds[1]) && $pathIds[1] != $curCategoryId) {
                return $pathIds[1];
            }
        }

        return null;
    }

    /**
     * @param Varien_Db_Select $select
     */
    protected function getMainAlias($select) {
        $from = $select->getPart(Varien_Db_Select::FROM);
        $aliases = array_keys($from);
        return $aliases[0];
    }

    public function getChildrenCollection($category, $mode) {

        /* @var $resource Mage_Catalog_Model_Resource_Eav_Mysql4_Category */
        $resource = $category->getResource();

        $categoryIds = array();
        switch ($mode) {
            case self::GET_ALL_CHILDREN_RECURSIVELY:
                $categoryIds = $resource->getChildren($category, true);
                break;
            case self::GET_ALL_DIRECT_CHILDREN:
                $categoryIds = $resource->getChildren($category, false);
                break;
            case self::GET_ALL_PARENTS:
                $categoryIds = array_filter(explode(',', $category->getPathInStore()));
                break;
        }

        $collection = $category->getCollection();
        $alias = $this->getMainAlias($collection->getSelect());

        /* @var $_conn Varien_Db_Adapter_Pdo_Mysql */
        $_conn = $collection->getConnection();

        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
//            $alias = 'main_table';
            $storeId = $category->getStoreId();
            $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns($alias . '.*')
                ->joinLeft(
                    array('url_rewrite' => $collection->getTable('core/url_rewrite')),
                        'url_rewrite.category_id=' . $alias . '.entity_id AND url_rewrite.is_system=1 AND ' .
                                $_conn->quoteInto(
                                    'url_rewrite.product_id IS NULL AND url_rewrite.store_id=? AND url_rewrite.id_path LIKE "category/%"',
                                    $storeId),
                    array('request_path' => 'url_rewrite.request_path'));
        }
        else {
//            $alias = 'e';
            $collection
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('all_children')
                ->addAttributeToSelect('is_anchor')
                ->joinUrlRewrite();
        }

        if ($this->coreHelper()->isManadevPaidLayeredNavigationInstalled()) {
            if ($this->flatHelper()->isEnabled() && $this->flatHelper()->isRebuilt() &&
                !$this->dbHelper()->indexRequiresReindexing('catalog_category_flat'))
            {
                $collection->addAttributeToFilter('m_show_in_layered_navigation', 1);
            }
            else {
                $res = Mage::getSingleton('core/resource');
                $showInLayeredNavigationTable = $res->getTableName('catalog_category_entity_int');
                $db = $collection->getConnection();

                $attributeSelect = $db->select()
                    ->from(array('a' => $res->getTableName('eav/attribute')), 'attribute_id')
                    ->join(array('t' => $res->getTableName('eav/entity_type')),
                        $db->quoteInto("`t`.`entity_type_id` = `a`.`entity_type_id`
                            AND `t`.`entity_type_code` = ?", 'catalog_category'), null)
                    ->where("`a`.`attribute_code` = ?", 'm_show_in_layered_navigation');
                $attributeId = $db->fetchOne($attributeSelect);

                $storeId = Mage::app()->getStore()->getId();
                $collection->getSelect()
                    ->joinLeft(array('m_siln_g' => $showInLayeredNavigationTable),
                        "`m_siln_g`.`entity_id` = `$alias`.`entity_id`
                            AND `m_siln_g`.`attribute_id` = $attributeId
                            AND `m_siln_g`.`store_id` = 0", null)
                    ->joinLeft(array('m_siln_s' => $showInLayeredNavigationTable),
                        "`m_siln_s`.`entity_id` = `$alias`.`entity_id`
                            AND `m_siln_s`.`attribute_id` = $attributeId
                            AND `m_siln_s`.`store_id` = $storeId", null)
                    ->where("COALESCE(`m_siln_s`.`value`, `m_siln_g`.`value`) = 1");
            }
        }

        if (count($categoryIds)) {
            $collection->getSelect()->where("`$alias`.`entity_id` IN (" . implode(', ', $categoryIds) . ")");
        }
        else {
            $collection->getSelect()->where("1 <> 1");
        }
        $collection->addAttributeToFilter('is_active', 1);
        $this->_orderCategoryItems($collection);
        $collection->load();

        return $collection;
    }

    protected function _orderCategoryItems($collection) {
        $collection->setOrder('position', 'ASC');
    }

    #region Dependencies

    /**
     * @return Mana_Filters_Helper_Data
     */
    public function filterHelper() {
        return Mage::helper('mana_filters');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return ManaPro_FilterDependent_Helper_Data
     */
    public function dependentHelper() {
        return Mage::helper('manapro_filterdependent');
    }

    /**
     * @return Mage_Catalog_Helper_Category_Flat
     */
    public function flatHelper() {
        return Mage::helper('catalog/category_flat');
    }

    /**
     * @return Mana_Core_Helper_Db
     */
    public function dbHelper() {
        return Mage::helper('mana_core/db');
    }
    #endregion
}