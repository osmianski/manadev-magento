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
            $preventRenderingCanonicalUrlWhenPagerIsApplied = false;
            $pageContainsLayeredNavigation = true;

            /* @var $head Mage_Page_Block_Html_Head */
            if ($this->coreHelper()->getRoutePath() == 'catalog/category/view') {
                $renderCanonicalUrl = $schema->getCanonicalCategory();
                if (!$renderCanonicalUrl) {
                    $renderCanonicalUrl = $this->catalogCategoryHelper()->canUseCanonicalTag();
                }
            }
            elseif ($this->coreHelper()->getRoutePath() == 'catalogsearch/result/index' && $this->coreHelper()->isManadevSeoLayeredNavigationInstalled()) {
                $renderCanonicalUrl = $schema->getCanonicalSearch();
            }
            elseif ($this->coreHelper()->getRoutePath() == 'cms/page/view' && $this->coreHelper()->isManadevSeoLayeredNavigationInstalled()) {
                $renderCanonicalUrl = $schema->getCanonicalCms();
            }
            elseif ($this->coreHelper()->getRoutePath() == 'cms/index/index' && $this->coreHelper()->isManadevSeoLayeredNavigationInstalled()) {
                $renderCanonicalUrl = $schema->getCanonicalCms();
            }
            elseif ($this->coreHelper()->getRoutePath() == 'mana/optionPage/view' && $this->coreHelper()->isManadevAttributePageInstalled()) {
                $renderCanonicalUrl = $schema->getCanonicalOptionPage();
            }
            elseif ($this->coreHelper()->getRoutePath() == 'mana_content/book/view' && $this->coreHelper()->isManadevCMSInstalled()) {
                $renderCanonicalUrl = $schema->getCanonicalBookPage();
                $pageContainsLayeredNavigation = false;
            }

            if ($renderCanonicalUrl) {
                $params = array('_nosid' => true, '_current' => true, '_m_escape' => '', '_use_rewrite' => true,
                    '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
                $originalQuery = $query = Mage::app()->getRequest()->getQuery();
                $areFiltersApplied = false;

                $filters = $pageContainsLayeredNavigation ? $this->_getFilters() : array();
                $isOtherParameterSuppressed = false;
                foreach (array_keys($query) as $key) {
                    if ($pageContainsLayeredNavigation) {
                        if (isset($filters[$key])) {
                            $areFiltersApplied = true;
                            if ($filters[$key]['include_in_canonical_url'] == 'never' ||
                                !$filters[$key]['include_in_canonical_url'] ||
                                $filters[$key]['include_in_canonical_url'] == 'as_in_schema' && (
                                    !$schema->getCanonicalFilters() ||
                                    !$this->coreHelper()->isManadevSeoLayeredNavigationInstalled())
                            ) {
                                $query[$key] = null;
                            }
                        }
                        elseif ($key == 'p') {
                            if (!$schema->getCanonicalPaging()) {
                                $query[$key] = null;
                            }
                        }
                        elseif ($key == 'q') {
                            // keep search query
                        }
                        else {
                            $query[$key] = null;
                            $isOtherParameterSuppressed = true;
                        }
                    }
                    else {
                        $query[$key] = null;
                    }
                }

                if ($this->coreHelper()->isManadevSeoLayeredNavigationInstalled()) {
                    if ($schema->getPrevNextProductList() && ($productList = $this->_getProductList($layout))
                        && ($areFiltersApplied || $this->_isProductListVisible()))
                    {
                        $toolbar = $productList->getToolbarBlock();
                        $collection = clone $productList->getLoadedProductCollection();

                        $collection->setCurPage($toolbar->getCurrentPage());
                        $limit = (int)$toolbar->getLimit();
                        $toolbar->unsetData('_current_limit');
                        if ($limit) {
                            $collection->setPageSize($limit);
                        }

                        $pageCount = $collection->getLastPageNumber();
                        $pageNo = $collection->getCurPage();
                        if ($pageNo > 1) {
                            $this->_removeHeadItemsByType($head, 'link_rel', 'rel="prev"');
                            $this->addLinkRel($head, 'prev', Mage::getUrl('*/*/*', array_merge($params,
                                        array('_query' => array_merge($query, array('p' => $pageNo - 1))))));
                            switch ($schema->getData('canonical_remove_when_pager_is_used')) {
                                case Mana_Seo_Model_Source_Canonical_HideWhenPagerIsUsed::ON_NON_FILTERED_PAGES_ONLY:
                                    $preventRenderingCanonicalUrlWhenPagerIsApplied = count($originalQuery) == 1 && isset($originalQuery['p']);
                                    break;
                                case Mana_Seo_Model_Source_Canonical_HideWhenPagerIsUsed::ON_ALL_PAGES_EXCEPT_HAVING_TOOLBAR_PARAMETERS:
                                    if ($pageContainsLayeredNavigation) {
                                        $preventRenderingCanonicalUrlWhenPagerIsApplied = !$isOtherParameterSuppressed;
                                    }
                                    else {
                                        $preventRenderingCanonicalUrlWhenPagerIsApplied = count($originalQuery) == 1 && isset($originalQuery['p']);
                                    }
                                    break;
                            }
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
                }


                $this->_removeHeadItemsByType($head, 'link_rel', 'rel="canonical"');
                if (!$preventRenderingCanonicalUrlWhenPagerIsApplied) {
                    $canonicalUrl = Mage::getUrl('*/*/*', array_merge($params, array('_query' => $query)));
                    $head->addLinkRel('canonical', $canonicalUrl);
                }
            }
        }
    }

    protected function _getFilters() {
        if ($this->_filters === null) {
            if ($this->coreHelper()->isManadevSeoLayeredNavigationInstalled()) {
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

    /**
     * Handles event "core_config_data_save_commit_after".
     * @param Varien_Event_Observer $observer
     */
    public function afterConfigDataSaveCommit($observer) {
        /* @var $configData Mage_Core_Model_Config_Data */
        $configData = $observer->getEvent()->getDataObject();

        $suffixFields = array(
            // category URL suffix redirects work without saving it in URL history so the following line is
            // disabled
            //Mana_Seo_Model_UrlHistory::TYPE_CATEGORY_SUFFIX => 'catalog/seo/category_url_suffix',

            Mana_Seo_Model_UrlHistory::TYPE_ATTRIBUTE_PAGE_SUFFIX => 'mana_attributepage/seo/attribute_page_url_suffix',
            Mana_Seo_Model_UrlHistory::TYPE_OPTION_PAGE_SUFFIX => 'mana_attributepage/seo/option_page_url_suffix',
        );

        foreach ($suffixFields as $historyType => $path) {
            $storeId = $configData->getStoreCode() ? Mage::app()->getStore($configData->getStoreCode())->getId() : 0;
            if ($configData->getPath() == $path &&
                Mage::getStoreConfig($configData->getPath(), $storeId) != $configData->getValue())
            {
                /* @var $history Mana_Seo_Model_UrlHistory */
                $history = $this->dbHelper()->getModel('mana_seo/urlHistory');
                $history
                    ->setData('url_key', $this->coreHelper()->addDotToSuffix(Mage::getStoreConfig($configData->getPath(), $storeId)))
                    ->setData('redirect_to', $this->coreHelper()->addDotToSuffix($configData->getValue()))
                    ->setData('type', $historyType)
                    ->setData('store_id', $storeId);
                $history->save();
            }
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
     * @return Mana_Db_Helper_Data
     */
    public function dbHelper() {
	    return Mage::helper('mana_db');
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