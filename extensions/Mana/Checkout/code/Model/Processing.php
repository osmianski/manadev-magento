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
class Mana_Checkout_Model_Processing extends Mage_Checkout_Model_Type_Onepage {
    protected $_failedFields;
    protected $_redirectUrl;

    /**
     * The whole order placement process is here
     */
    public function placeOrder() {
        try {
            // identify checkout method
            $this->getCheckoutMethod();

            // save quote's billing address
            $this->_processBillingAddress();

            // if different, save quote's shipping address
            if (!$this->getParam('billing[use_for_shipping]')) {
                $this->_processShippingAddress();
            }

            // save quote's shipping method (and additional options if provided)
            $this->_processShippingMethod();

            // save quote's payment method and additional data if provided
            $this->_processPaymentMethod();

            // save the order from quote, create customer if necessary, sent him email and similar stuff
            $this->_processOrder();

            // comments to the order
            $this->_processComments();

        }
        catch (Exception $e) {
            // in case of any error
            $result = array(
                'error' => $e->getMessage(),
            );
            if ($this->_failedFields) {
                $result['fields'] = $this->_failedFields;
            }
            return $result;
        }

        // in case of success
        $result = array('success' => true);
        if ($this->_redirectUrl) {
            $result['redirect'] = $this->_redirectUrl;
        }
        else {
            $result['redirect'] = Mage::getUrl('*/onepage/success');
        }
        return $result;
    }

    #region Processing methods
    protected function _processBillingAddress() {
        $data = $this->getRequest()->getPost('billing', array());
        $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
        $this->_fixAddressData($data);
        $result = $this->saveBilling($data, $customerAddressId);
        if (isset($result['error'])) {
            throw new Mana_Checkout_Exception($result['message']);
        }
        if (isset($result['redirect'])) {
            $this->_redirectUrl = $result['redirect'];
        }
    }
    protected function _processShippingAddress() {
        $data = $this->getRequest()->getPost('shipping', array());
        $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
        $this->_fixAddressData($data);
        $result = $this->saveShipping($data, $customerAddressId);
        if (isset($result['error'])) {
            throw new Mana_Checkout_Exception($result['message']);
        }
        if (isset($result['redirect'])) {
            $this->_redirectUrl = $result['redirect'];
        }
    }
    protected function _processShippingMethod() {
        $data = $this->getRequest()->getPost('shipping_method', '');
        $result = $this->saveShippingMethod($data);
        if (!$result) {
            Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array(
                'request' => $this->getRequest(),
                'quote' => $this->getQuote()
            ));
        }
        if (isset($result['error'])) {
            throw new Mana_Checkout_Exception($result['message']);
        }
        if (isset($result['redirect'])) {
            $this->_redirectUrl = $result['redirect'];
        }
    }
    protected function _processPaymentMethod() {
        try {
            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->savePayment($data);
            if (isset($result['error'])) {
                throw new Mage_Core_Exception($result['error']);
            }
            if (isset($result['redirect'])) {
                $this->_redirectUrl = $result['redirect'];
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $this->_failedFields = $e->getFields();
            }
            throw new Mana_Checkout_Exception($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            throw new Mana_Checkout_Exception($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Mana_Checkout_Exception($this->__('Unable to set Payment Method.'));
        }
    }
    protected function _processOrder() {
        try {
            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    throw new Mana_Checkout_Exception($this->__('Please agree to all the terms and conditions before placing the order.'));
                }
            }
            if ($data = $this->getRequest()->getPost('payment', false)) {
                $this->getQuote()->getPayment()->importData($data);
            }
            $this->saveOrder();
            $this->_redirectUrl = $this->getCheckout()->getRedirectUrl();
        } catch (Mage_Core_Exception $e) {
            throw new Mana_Checkout_Exception($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Mana_Checkout_Exception($this->__('There was an error processing your order. Please contact us or try again later.'));
        }
        $this->getQuote()->save();
    }

    protected function _processComments() {
        $data = $this->getRequest()->getPost('message', array());
        if (!empty($data['send'])) {
            $this->getLastOrder()
                ->addStatusHistoryComment(nl2br($this->__("Customer asked to leave a personal message. \n\n From: %s\nTo: %s\n\n%s", $data['from'], $data['to'], $data['text'])));
            $this->getLastOrder()->save();
        }
    }
    #endregion
    #region Overrides

    protected $_checkoutMethod;

    /**
     * Get quote checkout method
     *
     * @return string
     */
    public function getCheckoutMethod() {
        if (!$this->_checkoutMethod) {
            if ($this->getCustomerSession()->isLoggedIn()) {
                $this->_checkoutMethod = self::METHOD_CUSTOMER;
            }
            elseif ($this->getParam('billing[create_account]')) {
                $this->_checkoutMethod = self::METHOD_REGISTER;
                $this->getQuote()->setCheckoutMethod($this->_checkoutMethod);
            }
            else {
                $this->_checkoutMethod = self::METHOD_GUEST;
                $this->getQuote()->setCheckoutMethod($this->_checkoutMethod);
            }
        }
        return $this->_checkoutMethod;
    }

    #endregion

    #region Helpers

    /**
     * @return Mage_Core_Controller_Request_Http
     */
    public function getRequest() {
        return Mage::app()->getRequest();
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getLastOrder() {
        return Mage::registry('m_last_order');
    }
    /**
     * @param string $name
     * @return string
     */
    public function getParam($name) {
        if (strpos($name, '[') !== false) {
            list($array, $key) = explode('[', substr($name, 0, strlen($name) - 1));
            $param = $this->getRequest()->getParam($array);
            return isset($param[$key]) ? $param[$key] : false;
        }
        else {
            return $this->getRequest()->getParam($name);
        }
    }
    protected function _fixAddressData(&$data) {
        if (isset($data['email'])) {
            $data['email'] = trim($data['email']);
        }
        if (isset($data['region']) && $data['region'] == 1) {
            unset($data['region']);
        }
        Mage::helper('mana_checkout')->implodeTelephone($data);
    }
    protected function __() {
        $args = func_get_args();
        return call_user_func_array(array(Mage::helper('mana_checkout'), '__'), $args);
    }
    #endregion
}