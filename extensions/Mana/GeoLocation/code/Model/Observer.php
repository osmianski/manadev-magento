<?php
/**
 * @category    Mana
 * @package     Mana_GeoLocation
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class Mana_GeoLocation_Model_Observer {
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "controller_action_layout_render_before_checkout_index_index")
     * @param Varien_Event_Observer $observer
     */
    public function addCheckoutUrl($observer) {
        Mage::helper('mana_core/js')->options('.m-checkout', array(
            'countryByEmailUrl' => Mage::getUrl('mana_geolocation/location/country', array(
                '_query' => array('email' => '__0__')
            )),
        ));
    }
}