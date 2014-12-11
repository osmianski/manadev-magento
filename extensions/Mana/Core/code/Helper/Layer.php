<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_Layer extends Mage_Core_Helper_Abstract {
    public function useSolrForNavigation() {
        if (Mage::registry('m_no_solr')) {
            return false;
        }
        if (!Mage::helper('core')->isModuleEnabled('Enterprise_Search')) {
            return false;
        }
        /* @var $helper Enterprise_Search_Helper_Data */
        $helper = Mage::helper('enterprise_search');

        return $helper->getIsEngineAvailableForNavigation();
    }

    public function useSolrForSearch() {
        if (!Mage::helper('core')->isModuleEnabled('Enterprise_Search')) {
            return false;
        }
        /* @var $helper Enterprise_Search_Helper_Data */
        $helper = Mage::helper('enterprise_search');

        return $helper->isThirdPartSearchEngine() && $helper->isActiveEngine();
    }

    public function useSolr() {
        switch ($this->getMode()) {
            case 'category':
                return $this->useSolrForNavigation();
            case 'search':
                return $this->useSolrForSearch();
            default:
                throw new Exception('Not implemented');
        }
    }

    /**
     * @param null $mode
     * @throws Exception
     * @return Mage_Catalog_Model_Layer
     */
    public function getLayer($mode = null) {
        if (!$mode) {
            $mode = $this->getMode();
        }
        switch ($mode) {
            case 'category':
                return Mage::getSingleton($this->useSolrForNavigation()
                        ? 'enterprise_search/catalog_layer'
                        : 'catalog/layer'
                );
            case 'search':
                return Mage::getSingleton($this->useSolrForSearch()
                        ? 'enterprise_search/search_layer'
                        : 'catalogsearch/layer'
                );
            default:
                throw new Exception('Not implemented');
        }
    }

    protected $_mode;

    public function getMode() {
        if ($this->_mode) {
            return $this->_mode;
        }
        elseif (in_array(Mage::helper('mana_core')->getRoutePath(), array('catalogsearch/result/index', 'manapro_filterajax/search/index'))) {
            return 'search';
        }
        else {
            return 'category';
        }
    }

    public function setMode($mode) {
        $this->_mode = $mode;

        return $this;
    }

}