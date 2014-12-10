<?php
/**
 * @category    Mana
 * @package     Mana_InfiniteScrolling
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class Mana_InfiniteScrolling_Model_Observer {
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_ajax_response")
     * @param Varien_Event_Observer $observer
     */
    public function renderAjaxResponse($observer) {
        /* @var $action string */ $action = $observer->getEvent()->getAction();
        /* @var $response Varien_Object */ $response = $observer->getEvent()->getResponse();

        if ($action == 'scroll') {
            $fromPage = Mage::registry('m_scroll_from_page');
            $pageCount = Mage::registry('m_scroll_page_count');
            for ($page = 0; $page < $pageCount; $page++) {
                $renderedPage = $this->getLayout()->getBlock('m_ajax_update')->toAjaxHtml($action);
            }
            $response->setIsHandled(true);
        }
    }
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "m_ajax_request")
     * @param Varien_Event_Observer $observer
     */
    public function prepareAjaxRequest($observer) {
        if (Mage::registry('m_current_ajax_action') == 'scroll') {
            Mage::register('m_scroll_from_page', Mage::helper('mana_core')
                ->sanitizeNumber(Mage::app()->getRequest()->getParam('from_page')));
            Mage::helper('mana_core')
                ->updateRequestParameter('from_page', '', Mage::app()->getRequest()->getParam('from_page'));

            Mage::register('m_scroll_page_count', Mage::helper('mana_core')
                    ->sanitizeNumber(Mage::app()->getRequest()->getParam('page_count')));
            Mage::helper('mana_core')
                    ->updateRequestParameter('page_count', '', Mage::app()->getRequest()->getParam('page_count'));
        }
    }
}