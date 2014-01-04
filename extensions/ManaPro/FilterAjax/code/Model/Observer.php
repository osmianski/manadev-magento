<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAjax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* BASED ON SNIPPET: Models/Observer */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods - handlers for
 * these events.
 * @author Mana Team
 *
 */
class ManaPro_FilterAjax_Model_Observer {
	/**
	 * REPLACE THIS WITH DESCRIPTION (handles event "m_ajax_options")
	 * @param Varien_Event_Observer $observer
	 */
	public function renderOptions($observer) {
	    /* @var $core Mana_Core_Helper_Data */
	    $core = Mage::helper('mana_core');

        /* @var $js Mana_Core_Helper_Js */
        $js = Mage::helper('mana_core/js');

        /* @var $layeredNavigation Mana_Filters_Helper_Data */
        $layeredNavigation = Mage::helper('mana_filters');

        /* @var $filterAjax ManaPro_FilterAjax_Helper_Data */
	    $filterAjax = Mage::helper('manapro_filterajax');

        /* @var $urlModel Mage_Core_Model_Url */
        $urlModel = Mage::getSingleton('core/url');

        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');

        /* @var $ajaxUpdateBlock Mana_Ajax_Block_Update */
        $ajaxUpdateBlock = $layout->getBlock('m_ajax_update');

        foreach ($filterAjax->getPageTypes() as $pageType) {
	        if ($pageType->matchRoute($core->getRoutePath())) {
                $unfilteredUrl = $layeredNavigation->getClearUrl(false, true, true, true);
                $suffix = $core->addDotToSuffix($pageType->getCurrentSuffix());
                if ($suffix && $core->endsWith($unfilteredUrl, $suffix)) {
                    $unfilteredUrl = substr($unfilteredUrl, 0, strlen($unfilteredUrl) - strlen($suffix));
                }
                $unfilteredUrl = array($unfilteredUrl);
                $exceptions = array();
                if ($layeredNavigation->isTreeVisible() && $core->getRoutePath() == 'catalog/category/view' &&
                    !Mage::getStoreConfigFlag('mana/ajax/disable_for_category_redirects'))
                {
                    /* @var $treeHelper ManaPro_FilterTree_Helper_Data */
                    $treeHelper = Mage::helper('manapro_filtertree');
                    foreach ($treeHelper->getRootCategory()->getChildrenCategories() as $category) {
                        /* @var $category Mage_Catalog_Model_Category */
                        $url = $urlModel->sessionUrlVar($category->getUrl());
                        if ($suffix && $core->endsWith($url, $suffix)) {
                            $url = substr($url, 0, strlen($url) - strlen($suffix));
                        }
                        $unfilteredUrl[] = $url;

                    }
                }
                if (Mage::getStoreConfigFlag('mana/ajax/disable_for_category_redirects')) {
                    foreach ($layeredNavigation->getLayer()->getCurrentCategory()->getChildrenCategories() as $category) {
                        /* @var $category Mage_Catalog_Model_Category */
                        $url = $urlModel->sessionUrlVar($category->getUrl());
                        if ($suffix && $core->endsWith($url, $suffix)) {
                            $url = substr($url, 0, strlen($url) - strlen($suffix));
                        }
                        $exceptions[] = $url;

                    }
                }
                $js
                    ->setConfig('layeredNavigation.ajax.urlKey', Mage::getStoreConfig('mana/ajax/url_key_filter'))
                    ->setConfig('layeredNavigation.ajax.routeSeparator', Mage::getStoreConfig('mana/ajax/route_separator_filter'))
                    ->setConfig('layeredNavigation.ajax.scrollToTop', Mage::getStoreConfigFlag('mana/ajax/scroll_to_top_filter'))
                    ->setConfig('layeredNavigation.ajax.containers', $ajaxUpdateBlock->getInterceptedLinkContainers())
                    ->setConfig('layeredNavigation.ajax.exceptions', $exceptions)
                    ->setConfig('layeredNavigation.ajax.exceptionPatterns', $ajaxUpdateBlock->getExceptions())
                    ->setConfig('url.unfiltered', $unfilteredUrl)
                    ->setConfig('url.suffix', $suffix);

                break;
            }
	    }
	}

    /**
     * Handles event "http_response_send_before".
     * @param Varien_Event_Observer $observer
     */
    public function unsetFlagInCatalogSession($observer) {
        if (Mage::registry('manapro_filterajax_request')) {
            Mage::unregister('manapro_filterajax_request');
            $this->getCatalogSession()->unsetData('manapro_filterajax_request');

        }
    }
    /* obsolete handlers. Kept here for easier upgrade */
    public function registerUrl($observer) { }
    public function ajaxCategoryView($observer) {}
    public function ajaxSearchResult($observer) {}
    public function markUpdatableHtml($observer) {}
    public function ajaxCmsIndex($observer) {}
    public function ajaxCmsPage($observer) {}


    #region Dependencies

    /**
     * @return Mage_Catalog_Model_Session
     */
    public function getCatalogSession() {
        Mage::getSingleton('core/session', array('name' => 'frontend'));

        return Mage::getSingleton('catalog/session');
    }
    #endregion
}