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
class Mana_Checkout_Model_Defaults {
    protected $_areDefaultsInserted = false;
    protected $_areTotalsAffected = false;
    protected $_isSaveNeeded = false;
    public function insertQuoteDefaults() {
        if (!$this->_areDefaultsInserted) {
            $this->_insertDefaultBillingAddress();
            $this->_insertDefaultShippingAddress();


            $this->_areDefaultsInserted = true;

            if ($this->_areTotalsAffected) {
                $this->getQuote()->collectTotals();
            }

            if ($this->_isSaveNeeded) {
                $this->getQuote()->save();
            }
        }
    }
    protected function _insertDefaultBillingAddress() {
        $address = $this->getQuote()->getBillingAddress();
        $isImported = false;
        if (!$address || !$address->getId()) {
            $address = Mage::getModel('sales/quote_address');
            $this->_isSaveNeeded = true;
        }

        if ($this->isCustomerLoggedIn()) {
            if (!$address->getCustomerAddressId()) {
                $defaultBillingAddress = $this->getCustomer()->getDefaultBillingAddress();
                if ($defaultBillingAddress && $defaultBillingAddress->getId()) {
                    $address->importCustomerAddress($defaultBillingAddress);
                    $isImported = true;
                    $this->_isSaveNeeded = true;
                    $this->_areTotalsAffected = true;

                    $defaultShippingAddress = $this->getCustomer()->getDefaultShippingAddress();
                    if ($defaultShippingAddress && $defaultShippingAddress->getId() == $defaultBillingAddress->getId()) {
                        $address->setSameAsBilling($defaultBillingAddress->getId() == $defaultShippingAddress->getId());
                    }
                }
            }
            $this->getQuote()->setBillingAddress($address);
        }
        if (!$isImported && $address->getSameAsBilling() != Mage::helper('mana_checkout')->getIsSameShippingAddress()) {
            $address->setSameAsBilling(Mage::helper('mana_checkout')->getIsSameShippingAddress());
            $this->_isSaveNeeded = true;
        }
    }

    protected function _insertDefaultShippingAddress() {
        $address = $this->getQuote()->getShippingAddress();
        if (!$address || !$address->getId()) {
            $address = Mage::getModel('sales/quote_address');
            $this->_isSaveNeeded = true;
        }

        if ($this->isCustomerLoggedIn()) {
            if (!$address->getCustomerAddressId()) {
                $defaultBillingAddress = $this->getCustomer()->getDefaultBillingAddress();
                $defaultShippingAddress = $this->getCustomer()->getDefaultShippingAddress();
                if ($defaultShippingAddress && $defaultShippingAddress->getId()) {
                    $address->importCustomerAddress($defaultShippingAddress);
                    $this->_isSaveNeeded = true;
                    $this->_areTotalsAffected = true;
                }
            }
            $this->getQuote()->setShippingAddress($address);
        }
    }

    #region Helpers
    public function isCustomerLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
    protected $_checkout;
    protected $_quote;
    protected $_customer;
    /**
     * Retrieve checkout session model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout() {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }
    /**
     * Retrieve sales quote model
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote() {
        if (empty($this->_quote)) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }
    /**
     * Get logged in customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer() {
        if (empty($this->_customer)) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->_customer;
    }
    #endregion
}