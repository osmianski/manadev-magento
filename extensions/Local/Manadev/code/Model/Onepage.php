<?php

class Local_Manadev_Model_Onepage extends Mage_Checkout_Model_Type_Onepage {
    protected function _prepareNewCustomerQuote()
    {
        $quote      = $this->getQuote();
        $billing    = $quote->getBillingAddress();
        $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

        //$customer = Mage::getModel('customer/customer');
        $customer = $quote->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */
        //$customerBilling = $billing->exportCustomerAddress();
        //$customer->addAddress($customerBilling);
        //$billing->setCustomerAddress($customerBilling);
        //$customerBilling->setIsDefaultBilling(true);
        //if ($shipping && !$shipping->getSameAsBilling()) {
        //    $customerShipping = $shipping->exportCustomerAddress();
        //    $customer->addAddress($customerShipping);
        //    $shipping->setCustomerAddress($customerShipping);
        //    $customerShipping->setIsDefaultShipping(true);
        //} else {
        //    $customerBilling->setIsDefaultShipping(true);
        //}

        Mage::helper('core')->copyFieldset('checkout_onepage_quote', 'to_customer', $quote, $customer);
        $customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));
        $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));
        $quote->setCustomer($customer)
            ->setCustomerId(true);
    }
}