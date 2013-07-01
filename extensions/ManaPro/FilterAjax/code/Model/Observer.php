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

	    foreach ($filterAjax->getPageTypes() as $pageType) {
	        if ($pageType->matchRoute($core->getRoutePath())) {
                $unfilteredUrl = $layeredNavigation->getClearUrl(false, true, true, true);
                $suffix = $core->addDotToSuffix($pageType->getCurrentSuffix());
                if ($suffix && $core->endsWith($unfilteredUrl, $suffix)) {
                    $unfilteredUrl = substr($unfilteredUrl, 0, strlen($unfilteredUrl) - strlen($suffix));
                }

                $js
                    ->setConfig('layeredNavigation.ajax.urlKey', Mage::getStoreConfig('mana/ajax/url_key_filter'))
                    ->setConfig('layeredNavigation.ajax.routeSeparator', Mage::getStoreConfig('mana/ajax/route_separator_filter'))
                    ->setConfig('layeredNavigation.ajax.scrollToTop', Mage::getStoreConfigFlag('mana/ajax/scroll_to_top_filter'))
                    ->setConfig('url.unfiltered', $unfilteredUrl)
                    ->setConfig('url.suffix', $suffix);

                break;
            }
	    }
	}

    /* obsolete handlers. Kept here for easier upgrade */
    public function registerUrl($observer) { }
    public function ajaxCategoryView($observer) {}
    public function ajaxSearchResult($observer) {}
    public function markUpdatableHtml($observer) {}
    public function ajaxCmsIndex($observer) {}
    public function ajaxCmsPage($observer) {}
}