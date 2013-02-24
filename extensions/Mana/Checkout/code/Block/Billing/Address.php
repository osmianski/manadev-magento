<?php
/**
 * @category    Mana
 * @package     Mana_Checkout
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Checkout_Block_Billing_Address extends Mage_Checkout_Block_Onepage_Billing {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mana/checkout/billing/address.phtml');
    }
    public function getAddress() {
        if (is_null($this->_address)) {
            Mage::getSingleton('mana_checkout/defaults')->insertQuoteDefaults();
            $this->_address = $this->getQuote()->getBillingAddress();
            Mage::helper('mana_checkout')->explodeTelephone($this->_address);
        }

        return $this->_address;
    }
}