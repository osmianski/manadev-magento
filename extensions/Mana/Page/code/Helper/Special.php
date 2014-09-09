<?php
/** 
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Page_Helper_Special extends Mage_Core_Helper_Abstract  {
    protected $_counts = array();
    protected $_applied;
    /**
     * @var Mana_Page_Model_SpecialFilter[]
     */
    protected $_specialFilters;
    /**
     * @var Mage_Core_Block_Template
     */
    protected $_specialOptionsBlock;

    /**
     * @return Mana_Page_Model_SpecialFilter[]
     */
    public function getSpecialFilters() {
        if (!$this->_specialFilters) {
            /* @var $resource Mana_Page_Resource_Special */
            $resource = Mage::getResourceSingleton('mana_page/special');

            $result = array();

            foreach ($resource->getData(Mage::app()->getStore()->getId()) as $id => $special) {
                if ($special['filter']) {
                    /* @var $specialFilter Mana_Page_Model_SpecialFilter */
                    $specialFilter = Mage::getModel('mana_page/specialFilter');

                    $specialFilter->addData($special);
                    $specialFilter->setData('is_applied', $this->isApplied($special['url_key']));
                    $specialFilter->setData('host_filter', $this->getFilterOptionsById($special['filter']));
                    $specialFilter->setData('special_filter_code', 'special_options_' . $specialFilter->getData('url_key'));
                    $specialFilter->setData('filter_options', new Varien_Object());
                    $result[] = $specialFilter;
                }
            }

            $this->_specialFilters = $result;
        }
        return $this->_specialFilters;
    }

    /**
     * @param Mana_Filters_Model_Query $query
     */
    public function registerSpecialFilters($query) {
        if (!$this->_specialFilters) {
            foreach ($this->getSpecialFilters() as $specialFilter) {
                $specialFilter->setData('query', $query);
                $query->addFilter($specialFilter->getData('special_filter_code'), $specialFilter);
            }
        }
    }

    public function getOptionData($code) {
        $result = array();

        foreach ($this->getSpecialFilters() as $filter) {
            $filter->addToHostFilterOptions($result, $code);
        }

        return $result;
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return array
     */
    public function countOnCollection($collection) {
        $key = spl_object_hash($collection);

        if (!isset($this->_counts[$key])) {
            $result = array();
            /* @var $resource Mana_Page_Resource_Special */
            $resource = Mage::getResourceSingleton('mana_page/special');

            $db = $collection->getConnection();

            $select = clone $collection->getSelect();
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->reset(Zend_Db_Select::ORDER);
            $select->reset(Zend_Db_Select::GROUP);
            $select->reset(Zend_Db_Select::LIMIT_COUNT);
            $select->reset(Zend_Db_Select::LIMIT_OFFSET);
            $select->columns("COUNT(`e`.`entity_id`)");

            foreach ($resource->getData(Mage::app()->getStore()->getId()) as $id => $special) {
                if ($special['filter']) {
                    $xml = new SimpleXMLElement($special['condition']);
                    $rule = $this->rule($xml);
                    $rule->join($select, $xml);
                    $select->where($rule->where($xml));

                    $result[$id] = $db->fetchOne($select);
                }
            }

            $this->_counts[$key] = $result;
        }

        return $this->_counts[$key];
    }

    public function isCounted($collection) {
        $key = spl_object_hash($collection);

        return isset($this->_counts[$key]);
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return $this
     */
    public function applyToCollection($collection) {
        /* @var $resource Mana_Page_Resource_Special */
        $resource = Mage::getResourceSingleton('mana_page/special');

        $select = $collection->getSelect();

        foreach ($resource->getData(Mage::app()->getStore()->getId()) as $special) {
            if ($special['filter'] && $this->isApplied($special['url_key'])) {
                    $xml = new SimpleXMLElement($special['condition']);
                    $rule = $this->rule($xml);
                    $rule->join($select, $xml);
                    $select->where($rule->where($xml));
            }
        }
    }

    public function addToState() {
        /* @var $resource Mana_Page_Resource_Special */
        $resource = Mage::getResourceSingleton('mana_page/special');

        foreach ($resource->getData(Mage::app()->getStore()->getId()) as $special) {
            if ($special['filter'] && $this->isApplied($special['url_key'])) {
                $this->filterHelper()->getLayer()->getState()->addFilter($this->_createItemEx(array(
                    'label' => $special['title'],
                    'special' => true,
                    'value' => $special['url_key'],
                    'm_selected' => true,
                    'm_show_selected' => false,
                ), $this->getFilterOptionsById($special['filter'])));
            }
        }
    }

    /**
     * @param SimpleXmlElement $xml
     * @return Mana_Page_Helper_Special_Rule
     */
    public function rule($xml) {
        return Mage::helper((string)Mage::getConfig()->getNode('mana_page/special/' . $xml->getName()));
    }

    public function getRequestVar() {
        return 'special-options';
    }

    public function getAppliedOptions() {
        if (!$this->_applied) {
            $values = explode('_', Mage::app()->getRequest()->getParam($this->getRequestVar()));

            $this->_applied = $values ? array_filter($values) : array();
        }
        return $this->_applied;
    }

    public function isApplied($urlKey) {
        return in_array($urlKey, $this->getAppliedOptions());
    }

    public function isAppliedInFilter($code) {
        /* @var $resource Mana_Page_Resource_Special */
        $resource = Mage::getResourceSingleton('mana_page/special');

        foreach ($resource->getData(Mage::app()->getStore()->getId()) as $special) {
            if ($special['filter'] && $this->getFilterCodeById($special['filter']) == $code && $this->isApplied($special['url_key'])) {
                return true;
            }
        }

        return false;
    }

    public function getFilterCodeById($id) {
        $collection = $this->layerHelper()->getFilterOptionsCollection();
        if ($filter = $this->coreHelper()->collectionFind($collection, 'global_id', $id)) {
            return $filter->getData('code');
        }
        else {
            return false;
        }
    }

    public function getFilterOptionsById($id) {
        $collection = $this->layerHelper()->getFilterOptionsCollection();
        if ($filter = $this->coreHelper()->collectionFind($collection, 'global_id', $id)) {
            return $filter;
        }
        else {
            return false;
        }
    }

    /**
     * @param Mana_Filters_Model_Item $item
     * @return string
     */
    public function getItemAddToFilterUrl($item) {
        $values = $this->getAppliedOptions();
        if (!$this->isApplied($item->getData('value'))) {
            $values[] = $item->getData('value');
        }

    	$query = array(
            $this->getRequestVar() => implode('_', $values),
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );

        $params = array('_current'=>true, '_m_escape' => '', '_use_rewrite'=>true, '_query'=>$query, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }

    /**
     * @param Mana_Filters_Model_Item $item
     * @return string
     */
    public function getItemReplaceInFilterUrl($item, $code) {
        $values = $this->getAppliedOptions();
        if ($item->getData('special')) {
            if (!$this->isApplied($item->getData('value'))) {
                $values[] = $item->getData('value');
            }

            $query = array(
                $this->getRequestVar() => implode('_', $values),
                $code => null,
                Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
            );

            $params = array('_current'=>true, '_m_escape' => '', '_use_rewrite'=>true, '_query'=>$query, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
            return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
        }
        else {
            /* @var $resource Mana_Page_Resource_Special */
            $resource = Mage::getResourceSingleton('mana_page/special');

            foreach ($resource->getData(Mage::app()->getStore()->getId()) as $special) {
                if ($special['filter'] && $this->getFilterCodeById($special['filter']) == $code && $this->isApplied($special['url_key'])) {
                    unset($values[array_search($special['url_key'], $values)]);
                }
            }

            $query = array(
                $this->getRequestVar() => count($values) > 0 ? implode('_', $values) : null,
                $code => $item->getData('value'),
                Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
            );

            $params = array('_current'=>true, '_m_escape' => '', '_use_rewrite'=>true, '_query'=>$query, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
            return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
        }
    }

    /**
     * @param Mana_Filters_Model_Item $item
     * @return string
     */
    public function getItemRemoveFromFilterUrl($item) {
        $values = $this->getAppliedOptions();
        if ($this->isApplied($item->getData('value'))) {
            unset($values[array_search($item->getData('value'), $values)]);
        }

    	$query = array(
            $this->getRequestVar() => count($values) > 0 ? implode('_', $values) : null,
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );

        $params = array('_current'=>true, '_m_escape' => '', '_use_rewrite'=>true, '_query'=>$query, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }

    public function getClearFilterUrl($code) {
        $values = $this->getAppliedOptions();

        /* @var $resource Mana_Page_Resource_Special */
        $resource = Mage::getResourceSingleton('mana_page/special');

        foreach ($resource->getData(Mage::app()->getStore()->getId()) as $special) {
            if ($special['filter'] && $this->getFilterCodeById($special['filter']) == $code && $this->isApplied($special['url_key'])) {
                unset($values[array_search($special['url_key'], $values)]);
            }
        }

        $query = array(
            $this->getRequestVar() => count($values) > 0 ? implode('_', $values) : null,
            $code => null,
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );

        $params = array('_current'=>true, '_m_escape' => '', '_use_rewrite'=>true, '_query'=>$query, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        return Mage::helper('mana_filters')->markLayeredNavigationUrl(Mage::getUrl('*/*/*', $params), '*/*/*', $params);
    }

    public function getSliderSpecialOptionsHtml($filter) {
        if ($filter->getSpecialItems() && count($filter->getSpecialItems())) {
            if (!$this->_specialOptionsBlock) {
                $this->_specialOptionsBlock = Mage::getSingleton('core/layout')->createBlock('core/template');
            }

            $this->_specialOptionsBlock->setTemplate($this->coreHelper()->isManadevLayeredNavigationCheckboxesInstalled()
                ? 'mana/filters/items/list_special.phtml'//'manapro/filtercheckboxes/cssitems_special.phtml'
                : 'mana/filters/items/list_special.phtml');

            $this->_specialOptionsBlock
                ->setData('filter', $filter)
                ->setData('filter_options', $filter->getData('filter_options'));

            return $this->_specialOptionsBlock->toHtml();
        }
        else {
            return '';
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
    public function layerHelper() {
        return Mage::helper('mana_filters');
    }

    /**
     * @return Mana_Filters_Helper_Data
     */
    public function filterHelper() {
        return Mage::helper('mana_filters');
    }

    #endregion
}