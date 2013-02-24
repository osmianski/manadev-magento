<?php
/**
 * @category    Mana
 * @package     Mana_Checkout
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class Mana_Checkout_Model_Observer {
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "checkout_type_onepage_save_order_after")
     * @param Varien_Event_Observer $observer
     */
    public function saveCreatedOrderInRegistry($observer) {
        Mage::register('m_last_order', $observer->getEvent()->getOrder());
    }
}