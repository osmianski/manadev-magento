<?php
/** 
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Local_Manadev_Model_Express_Checkout extends Mage_Paypal_Model_Express_Checkout {
    public function returnFromPaypal($token) {
        $this->_getApi();
        $this->_api->setToken($token)
            ->callGetExpressCheckoutDetails();
        $quote = $this->_quote;

        // import billing address
        $billingAddress = $quote->getBillingAddress();
        $exportedBillingAddress = $this->_api->getExportedBillingAddress();
        $quote->setCustomerEmail($billingAddress->getEmail());
        $quote->setCustomerPrefix($billingAddress->getPrefix());
        $quote->setCustomerFirstname($billingAddress->getFirstname());
        $quote->setCustomerMiddlename($billingAddress->getMiddlename());
        $quote->setCustomerLastname($billingAddress->getLastname());
        $quote->setCustomerSuffix($billingAddress->getSuffix());
        $quote->setCustomerNote($exportedBillingAddress->getData('note'));
        foreach ($exportedBillingAddress->getExportedKeys() as $key) {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                switch ($key) {
                    case 'firstname':
                        $billingAddress->setDataUsingMethod($key,
                            $exportedBillingAddress->getData('firstname') .
                            (trim($exportedBillingAddress->getData('middlename')) ? ' ' . $exportedBillingAddress->getData('middlename') : '') .
                            (trim($exportedBillingAddress->getData('lastname')) ? ' ' . $exportedBillingAddress->getData('lastname') : '')
                        );
                        break;
                    case 'middlename':
                        $billingAddress->setDataUsingMethod($key, '');
                        break;
                    case 'lastname':
                        $billingAddress->setDataUsingMethod($key, '');
                        break;
                    default:
                        $billingAddress->setDataUsingMethod($key, $exportedBillingAddress->getData($key));
                        break;
                }
            }
        }

        // import shipping address
        $exportedShippingAddress = $this->_api->getExportedShippingAddress();
        if (!$quote->getIsVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress) {
                if ($exportedShippingAddress) {
                    foreach ($exportedShippingAddress->getExportedKeys() as $key) {
                        $shippingAddress->setDataUsingMethod($key, $exportedShippingAddress->getData($key));
                    }
                    $shippingAddress->setCollectShippingRates(true);
                    $shippingAddress->setSameAsBilling(0);
                }

                // import shipping method
                $code = '';
                if ($this->_api->getShippingRateCode()) {
                    if ($code = $this->_matchShippingMethodCode($shippingAddress, $this->_api->getShippingRateCode())) {
                        // possible bug of double collecting rates :-/
                        $shippingAddress->setShippingMethod($code)->setCollectShippingRates(true);
                    }
                }
                $quote->getPayment()->setAdditionalInformation(
                    self::PAYMENT_INFO_TRANSPORT_SHIPPING_METHOD,
                    $code
                );
            }
        }
        $this->_ignoreAddressValidation();

        // import payment info
        $payment = $quote->getPayment();
        $payment->setMethod($this->_methodType);
        Mage::getSingleton('paypal/info')->importToPayment($this->_api, $payment);
        $payment->setAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_PAYER_ID, $this->_api->getPayerId())
            ->setAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_TOKEN, $token);
        $quote->collectTotals()->save();
    }

    private function _ignoreAddressValidation() {
        $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->_quote->getIsVirtual()) {
            $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
        }
    }

    /*    public function returnFromPaypal($token) {
            parent::returnFromPaypal($token);

            $quote = $this->_quote;
            $billingAddress = $quote->getBillingAddress();
            $billingAddress->setFirstname(
                $billingAddress->getFirstname() .
                (trim($billingAddress->getMiddlename()) ? ' ' . $billingAddress->getMiddlename() : '') .
                (trim($billingAddress->getLastname()) ? ' ' . $billingAddress->getLastname() : '')
            );
            $billingAddress->setMiddlename('');
            $billingAddress->setLastname('');

            $quote->setCustomerFirstname($billingAddress->getFirstname());
            $quote->setCustomerMiddlename($billingAddress->getMiddlename());
            $quote->setCustomerLastname($billingAddress->getLastname());

            $quote->collectTotals()->save();

        }*/
}