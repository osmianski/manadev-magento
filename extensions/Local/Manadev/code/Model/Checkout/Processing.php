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
class Local_Manadev_Model_Checkout_Processing extends Mana_Checkout_Model_Processing {
    protected function _processBillingAddress() {
        if ($address = Mage::helper('local_manadev')->getCustomerAddress()) {
            $_POST['billing_address_id'] = $address->getId();
        }
        parent::_processBillingAddress();
    }
    protected function _processShippingMethod() {
        return;
    }

    protected function _processPaymentMethod() {
        $_POST['payment'] = array(
            'method' => 'paypal_standard',
            //'method' => 'checkmo',
        );
        parent::_processPaymentMethod();
    }
    protected function _processOrder() {
        parent::_processOrder();

        if ($address = Mage::helper('local_manadev')->getCustomerAddress()) {
            /* @var $address Mage_Customer_Model_Address */
            Mage::helper('core')->copyFieldset('m_customer_address', 'from_quote_address',
                $this->getLastOrder()->getBillingAddress(), $address);
            $address->save();

            /* @var $customer Mage_Customer_Model_Customer */
            $customer = $address->getCustomer();
            $taxClassId = $customer->getTaxClassId();
            $correctTaxClassId = Mage::helper('mana_vat')->getCustomerClassId(
                Mage::getSingleton('checkout/session')->getMIsVatValid()
            );
            if ($taxClassId != $correctTaxClassId) {
                $customerGroup = Mage::getModel('customer/group')->load($customer->getGroupId());
                $taxIndependentGroupCode = $customerGroup->getTaxIndependentCode();
                if ($correctCustomerGroupId = Mage::helper('local_manadev')->findCustomerGroupIdByTaxClassAndCode($correctTaxClassId, $taxIndependentGroupCode)) {
                    $customer->setGroupId($correctCustomerGroupId)->save();
                    Mage::getSingleton('customer/session')->setCustomerGroupId($correctCustomerGroupId);
                }
            }
        }
    }

    public function saveBilling($data, $customerAddressId) {
        if (empty($data)) {
            return array('error' => -1, 'message' => $this->_helper->__('Invalid data.'));
        }

        $address = $this->getQuote()->getBillingAddress();
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
                ->setEntityType('customer_address')
                ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());

        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => $this->_helper->__('Customer Address is not valid.')
                    );
                }

                $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                $addressForm->setEntity($address);
                $addressErrors = $addressForm->validateData($address->getData());
                if ($addressErrors !== true) {
                    return array('error' => 1, 'message' => $addressErrors);
                }
            }
        }

        $addressForm->setEntity($address);
        // emulate request object
        $addressData = $addressForm->extractData($addressForm->prepareRequest($data));
        $addressErrors = $addressForm->validateData($addressData);
        if ($addressErrors !== true) {
            return array('error' => 1, 'message' => $addressErrors);
        }
        $addressForm->compactData($addressData);
        //unset billing address attributes which were not shown in form
        foreach ($addressForm->getAttributes() as $attribute) {
            if (!isset($data[$attribute->getAttributeCode()])) {
                $address->setData($attribute->getAttributeCode(), NULL);
            }
        }

        if (empty($customerAddressId)) {
            // Additional form data, not fetched by extractData (as it fetches only attributes)
            $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
        }
        // validate billing address
        if (($validateRes = $address->validate()) !== true) {
            return array('error' => 1, 'message' => $validateRes);
        }

        $address->implodeStreetAddress();

        if (true !== ($result = $this->_validateCustomerData($data))) {
            return $result;
        }

        if (!$this->getQuote()->getCustomerId() && self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
            if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                return array('error' => 1, 'message' => $this->_helper->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.'));
            }
        }

        if (!$this->getQuote()->isVirtual()) {
            /**
             * Billing address using otions
             */
            $usingCase = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;

            switch ($usingCase) {
                case 0:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    break;
                case 1:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();

                    // don't reset original shipping data, if it was not changed by customer
                    foreach ($shipping->getData() as $shippingKey => $shippingValue) {
                        if (!is_null($shippingValue)
                                && !is_null($billing->getData($shippingKey))
                                && !isset($data[$shippingKey])
                        ) {
                            $billing->unsetData($shippingKey);
                        }
                    }
                    $shipping->addData($billing->getData())
                            ->setSameAsBilling(1)
                            ->setSaveInAddressBook(0)
                            ->setShippingMethod($shippingMethod)
                            ->setCollectShippingRates(true);
                    $this->getCheckout()->setStepData('shipping', 'complete', true);
                    break;
            }
        }

        $this->getQuote()->collectTotals();
        $this->getQuote()->save();

        if (!$this->getQuote()->isVirtual() && $this->getCheckout()->getStepData('shipping', 'complete') == true) {
            //Recollect Shipping rates for shipping methods
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        }

        $this->getCheckout()
                ->setStepData('billing', 'allow', true)
                ->setStepData('billing', 'complete', true)
                ->setStepData('shipping', 'allow', true);

        return array();
    }
}