<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - 
 * handlers for these events.
 * @author Mana Team
 *
 */
class Mana_Seo_Model_Observer {
    protected $_filters;
	/**
	 * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_layout_generate_blocks_after")
	 * @param Varien_Event_Observer $observer
	 */
	public function prepareMetaData($observer) {
        /* @var $action Mage_Core_Controller_Varien_Action */
        $action = $observer->getEvent()->getData('action');
        /* @var $layout Mage_Core_Model_Layout */
        $layout = $observer->getEvent()->getData('layout');

        if (($head = $layout->getBlock('head')) &&
            ($schema = $this->seoHelper()->getActiveSchema(Mage::app()->getStore()->getId()))) {
            $renderCanonicalUrl = false;

            /* @var $head Mage_Page_Block_Html_Head */
            if ($this->coreHelper()->getRoutePath() == 'catalog/category/view') {
                $renderCanonicalUrl = $schema->getCanonicalCategory();
                if (!$renderCanonicalUrl) {
                    $renderCanonicalUrl = $this->catalogCategoryHelper()->canUseCanonicalTag();
                }
            }
            elseif ($this->coreHelper()->getRoutePath() == 'catalogsearch/result/index') {
                $renderCanonicalUrl = $schema->getCanonicalSearch();
            }
            elseif ($this->coreHelper()->getRoutePath() == 'cms/page/view') {
                $renderCanonicalUrl = $schema->getCanonicalCms();
            }
            elseif ($this->coreHelper()->getRoutePath() == 'cms/index/index') {
                $renderCanonicalUrl = $schema->getCanonicalCms();
            }
            elseif ($this->coreHelper()->getRoutePath() == 'mana/optionPage/view') {
                $renderCanonicalUrl = $schema->getCanonicalOptionPage();
            }

            if ($renderCanonicalUrl) {
                $params = array('_nosid' => true, '_current' => true, '_m_escape' => '', '_use_rewrite' => true,
                    '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
                $query = Mage::app()->getRequest()->getQuery();
                $areFiltersApplied = false;
                $filters = $this->_getFilters();
                foreach (array_keys($query) as $key) {
                    if (isset($filters[$key])) {
                        $areFiltersApplied = true;
                        if ($filters[$key]['include_in_canonical_url'] == 'never' ||
                            $filters[$key]['include_in_canonical_url'] == 'as_in_schema' && !$schema->getCanonicalFilters())
                        {
                            $query[$key] = null;
                        }
                    }
                    else {
                        $query[$key] = null;
                    }
                }
                if ($schema->getPrevNextProductList() && ($productList = $this->_getProductList($layout))
                    && ($areFiltersApplied || $this->_isProductListVisible()))
                {
                    $toolbar = $productList->getToolbarBlock();
                    $collection = clone $productList->getLoadedProductCollection();

                    $collection->setCurPage($toolbar->getCurrentPage());
                    $limit = (int)$toolbar->getLimit();
                    if ($limit) {
                        $collection->setPageSize($limit);
                    }

                    $pageCount = $collection->getLastPageNumber();
                    $pageNo = $collection->getCurPage();
                    if ($pageNo > 1) {
                        $this->_removeHeadItemsByType($head, 'link_rel', 'rel="prev"');
                        $this->addLinkRel($head, 'prev', Mage::getUrl('*/*/*', array_merge($params,
                            array('_query' => array_merge($query, array('p' => $pageNo - 1))))));
                    }
                    if ($pageNo < $pageCount) {
                        $this->_removeHeadItemsByType($head, 'link_rel', 'rel="next"');
                        $head->addLinkRel('next', Mage::getUrl('*/*/*', array_merge($params,
                            array('_query' => array_merge($query, array('p' => $pageNo + 1))))));
                    }
                    if ($schema->getCanonicalLimitAll() && Mage::getStoreConfigFlag('catalog/frontend/list_allow_all')) {
                        $query['limit'] = 'all';
                    }
                }

                $canonicalUrl =  Mage::getUrl('*/*/*', array_merge($params, array('_query' => $query)));
                $this->_removeHeadItemsByType($head, 'link_rel', 'rel="canonical"');
                $head->addLinkRel('canonical', $canonicalUrl);
            }
        }
    }

    protected function _getFilters() {
        if ($this->_filters === null) {
            if ($this->coreHelper()->isManadevLayeredNavigationInstalled()) {
                $this->_filters = array();
                foreach ($this->filterHelper()->getFilterOptionsCollection(true) as $filter) {
                    /* @var $filter Mana_Filters_Model_Filter2_Store */
                    $this->_filters[$filter->getType() == 'category' ? 'cat' : $filter->getCode()] = array(
                        'include_in_canonical_url' => $filter->getData('include_in_canonical_url'),
                    );
                }
            }
            else {
                /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection */
                $collection = Mage::getResourceModel('catalog/product_attribute_collection');
                $collection->addIsFilterableFilter();
                $this->_filters = array('cat' => array(
                    'include_in_canonical_url' => 'as_in_schema'
                ));
                foreach ($collection as $attribute) {
                    /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                    $this->_filters[$attribute->getAttributeCode()] = array(
                        'include_in_canonical_url' => 'as_in_schema'
                    );
                }
            }
        }

        return $this->_filters;
    }

    /**
     * @param Mage_Core_Model_Layout $layout
     * @return bool|Mage_Catalog_Block_Product_List
     */
    protected function _getProductList($layout) {
        foreach (array('product_list', 'search_result_list') as $blockName) {
            if ($result = $layout->getBlock($blockName)) {
                return $result;
            }
        }
        return false;
    }
    /**
     * @param Mage_Page_Block_Html_Head $head
     * @param string $type
     * @param bool|string $params
     */
    protected function _removeHeadItemsByType($head, $type, $params = false) {
        $data = $head->getData();
        $data = isset($data['items']) ? $data['items'] : array();
        foreach (array_keys($data) as $key) {
            if ($this->coreHelper()->startsWith($key, $type.'/')) {
                if ($params) {
                    if (isset($data[$key]['params'])) {
                        if ($params == $data[$key]['params']) {
                            unset($data[$key]);
                        }
                    }
                }
                else {
                    unset($data[$key]);
                }
            }
        }
        $head->setData('items', $data);
	}

    public function getActiveFilters() {
        $filters = $this->layerHelper()->getLayer()->getState()->getFilters();
        if (!is_array($filters)) {
            $filters = array();
        }

        return $filters;
    }

    /**
     * @param Mage_Page_Block_Html_Head $head
     * @param string $rel
     * @param string $url
     */
    public function addLinkRel($head, $rel, $url) {
        $items = $head->getData('items');
        $items['link_rel_prev/' . $url] = array(
            'type' => 'link_rel',
            'name' => $url,
            'params' => 'rel="' . $rel . '"',
            'if' => null,
            'cond' => null,
        );
        $head->setData('items', $items);
    }

    protected function _isProductListVisible() {
        if ($pageType = $this->coreHelper()->getPageTypeByRoutePath()) {
            return $pageType->isProductListVisible();
        }
        else {
            return false;
        }
    }


    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
	    return Mage::helper('mana_core');
	}

    /**
     * @return Mana_Filters_Helper_Data
     */
    public function filterHelper() {
        return Mage::helper('mana_filters');
    }

    /**
     * @return Mana_Seo_Helper_Data
     */
    public function seoHelper() {
        return Mage::helper('mana_seo');
    }

    /**
     * @return Mage_Catalog_Helper_Category
     */
    public function catalogCategoryHelper() {
        return Mage::helper('catalog/category');
    }

    /**
     * @return Mana_Core_Helper_Layer
     */
    public function layerHelper() {
        return Mage::helper('mana_core/layer');
    }

    #endregion
}