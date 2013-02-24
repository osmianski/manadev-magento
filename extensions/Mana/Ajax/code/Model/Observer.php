<?php
/**
 * @category    Mana
 * @package     Mana_Ajax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class Mana_Ajax_Model_Observer {
    /**
     * If AJAX is enabled on page, raises global flag to wrap all AJAX-able blocks into container DIV elements.
     * If in addition, current request asks only for AJAX content, cancels full page rendering and instead
     * raises global flag to render only AJAX content (handles event "controller_action_predispatch")
     * @param Varien_Event_Observer $observer
     */
    public function ajaxifyPage($observer) {
        /* @var $controller Mage_Core_Controller_Varien_Action */ $controller = $observer->getEvent()->getControllerAction();
        $actionName = $controller->getFullActionName();
        if ($allowedAjaxActions = Mage::helper('mana_ajax')->getAllowedActions($actionName)) {
            Mage::register('m_wrap_updatable_html_blocks', true);
            if (($ajaxAction = $controller->getRequest()->getParam('m-ajax')) && in_array($ajaxAction, $allowedAjaxActions)) {
                Mage::helper('mana_core')->updateRequestParameter('m-ajax', '', $ajaxAction);
                Mage::helper('mana_core')->updateRequestParameter('no_cache', '', '1');
                Mage::register('m_current_ajax_action', $ajaxAction);

                Mage::dispatchEvent('m_ajax_request');
                Mage::app()->getFrontController()->setNoRender(true);
            }
        }
    }

    /**
     * If relevant global flag is raised, wrap all AJAX-able blocks into container DIV elements
     * (handles event "core_block_abstract_to_html_after")
     * @param Varien_Event_Observer $observer
     */
    public function wrapUpdatableHtml($observer) {
        if (Mage::registry('m_wrap_updatable_html_blocks')) {
            /* @var $block Mage_Core_Block_Abstract */ $block = $observer->getEvent()->getBlock();
            /* @var $transport Varien_Object */ $transport = $observer->getEvent()->getTransport();

            if ($block->getLayout() && ($updateBlock = $block->getLayout()->getBlock('m_ajax_update'))) {
                $transport->setHtml($updateBlock->markUpdatable($block->getNameInLayout(), $transport->getHtml()));
            }
        }
    }

    /**
     * If relevant global flag is raised, renders AJAX content into JSON response instead of typical full-page
     * HTML response (handles event "controller_front_send_response_before")
     * @param Varien_Event_Observer $observer
     */
    public function renderAjaxResponse($observer) {
        if ($action = Mage::registry('m_current_ajax_action')) {
            $response = new Varien_Object();
            Mage::dispatchEvent('m_ajax_response', compact('action', 'response'));
            if ($response->getIsHandled()) {
                $response = $response->getData();
                unset($response['is_handled']);
            }
            else {
                $response = $this->getLayout()->getBlock('m_ajax_update')->toAjaxHtml($action);
            }
            $this->getResponse()->setBody(json_encode($response));
        }
    }

    /**
     * Retrieve current layout object
     *
     * @return Mage_Core_Model_Layout
     */
    public function getLayout() {
        return Mage::getSingleton('core/layout');
    }
    /**
     * Retrieve response object
     *
     * @return Mage_Core_Controller_Response_Http
     */
    public function getResponse() {
        return Mage::app()->getResponse();
    }
}