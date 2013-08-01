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
    #region Obsolete event handlers
    /**
     * If AJAX is enabled on page, raises global flag to wrap all AJAX-able blocks into container DIV elements.
     * If in addition, current request asks only for AJAX content, cancels full page rendering and instead
     * raises global flag to render only AJAX content (handles event "controller_action_predispatch")
     * @param Varien_Event_Observer $observer
     */
    public function ajaxifyPage($observer) {
    }

    /**
     * If relevant global flag is raised, wrap all AJAX-able blocks into container DIV elements
     * (handles event "core_block_abstract_to_html_after")
     * @param Varien_Event_Observer $observer
     */
    public function wrapUpdatableHtml($observer) {
    }

    /**
     * If relevant global flag is raised, renders AJAX content into JSON response instead of typical full-page
     * HTML response (handles event "controller_front_send_response_before")
     * @param Varien_Event_Observer $observer
     */
    public function renderAjaxResponse($observer) {
    }
    #endregion
}