<?php
/**
 * @category    Mana
 * @package     Mana_Content
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
class Mana_Content_Model_Observer {
	/**
	 * Sets data that will be used on client side AJAX (handles event "m_ajax_options")
	 * @param Varien_Event_Observer $observer
	 */
	public function setAjaxData($observer) {
        /* @var $js Mana_Core_Helper_Js */
        $js = Mage::helper('mana_core/js');
        $params = Mage::app()->getFrontController()->getRequest()->getParams();
        $params['_use_rewrite'] = true;
        $params['_nosid'] = true;
        $url = Mage::getUrl('mana_content/book/view', $params);

        $js
            ->setConfig('mana_content.url', $url)
            ->setConfig('mana_content.ajax.urlKey', Mage::getStoreConfig('mana_content/ajax/url_key_filter'))
            ->setConfig('mana_content.ajax.routeSeparator', Mage::getStoreConfig('mana_content/ajax/route_separator_filter'))
            ->setConfig('mana_content.ajax.containers', Mage::getStoreConfig('mana_content/ajax/containers'));
	}

    /* obsolete handlers. Kept here for easier upgrade */
    public function registerUrl($observer) { }
    public function ajaxCategoryView($observer) {}
    public function ajaxSearchResult($observer) {}
    public function markUpdatableHtml($observer) {}
    public function ajaxCmsIndex($observer) {}
    public function ajaxCmsPage($observer) {}
}