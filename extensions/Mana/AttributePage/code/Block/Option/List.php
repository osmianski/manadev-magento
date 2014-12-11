<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Block_Option_List extends Mage_Core_Block_Template {
    protected $_collection;

    protected function _construct() {
        parent::_construct();
        switch (Mage::getStoreConfig('mana_attributepage/attribute_page_settings/template')) {
            case 'template1': $this->setTemplate('mana/attributepage/option/list.phtml'); break;
            case 'template2': $this->setTemplate('mana/attributepage/option/list2.phtml'); break;
        }

    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /* @var $pager Mage_Page_Block_Html_Pager */
        $pager = $this->getLayout()->createBlock('page/html_pager', 'option_list_pager');
        $pager->setAvailableLimit($this->getAvailableLimit());
        $pager
            ->setCollection($this->getCollection())
            ->setLimit($this->getLimit())
            ->setLimitVarName($this->getLimitVarName())
            ->setPageVarName($this->getPageVarName());
        $this->setChild('pager', $pager);
        return $this;
    }

    public function getAvailableLimit() {
        $result = array();
        foreach (explode(',', $this->getAttributePage()->getData('allowed_page_sizes')) as $pageSize) {
            if ($pageSize = trim($pageSize)) {
                $result[$pageSize] = $pageSize == 'all' ? $this->__('All') : $pageSize;
            }
        }
        return $result;
    }

    public function getLimitVarName() {
        return 'limit';
    }

    public function getPageVarName() {
        return 'p';
    }

    public function getCurrentPage() {
        if ($page = (int)$this->getRequest()->getParam($this->getPageVarName())) {
            return $page;
        }

        return 1;
    }

    public function getLimit() {
        $limits = $this->getAvailableLimit();
        $defaultLimit = $this->getAttributePage()->getData('default_page_size');
        if (!$defaultLimit || !isset($limits[$defaultLimit])) {
            $keys = array_keys($limits);
            $defaultLimit = $keys[0];
        }

        $limit = $this->getRequest()->getParam($this->getLimitVarName());
        if (!$limit || !isset($limits[$limit])) {
            $limit = $defaultLimit;
        }

        return $limit;
    }

    public function getPagerHtml() {
        if (count($this->getAvailableLimit()) > 1 || $this->getCollection()->getLastPageNumber() > 1) {
            return $this->getChildHtml('pager');
        }
        else {
            return '';
        }
    }

    public function getCollection() {
        if (!$this->_collection) {
            $collection = $this->getAttributePage()->getOptionPages();

            // set paging parameters
            $collection->setCurPage($this->getCurrentPage());
            if ($limit = (int)$this->getLimit()) {
                $collection->setPageSize($limit);
            }

            // set letter filter
            if (($alpha = Mage::app()->getRequest()->getParam('alpha')) !== null) {
                $collection->addAlphaFilter($alpha == '0' ? '#' : $alpha);
            }

            // set having products filter
            if ($this->getAttributePage()->getData('hide_empty_option_pages')) {
                $collection->addHavingProductsFilter();
            }

            $this->_collection = $collection;
        }

        return $this->_collection;
    }

    public function getCount() {
        return $this->getCollection()->count();
    }

    #region Dependencies
    /**
     * @return Mana_AttributePage_Model_AttributePage_Store
     */
    public function getAttributePage() {
        return Mage::registry('current_attribute_page');
    }

    /**
     * @return Mana_Core_Helper_Files
     */
    public function filesHelper() {
        return Mage::helper('mana_core/files');
    }
    #endregion
}