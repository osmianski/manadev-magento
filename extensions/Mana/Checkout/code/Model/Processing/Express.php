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
class Mana_Checkout_Model_Processing_Express extends Local_Manadev_Model_Checkout_Processing {
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

            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    throw new Mana_Checkout_Exception(
                        $this->__('Please agree to all the terms and conditions before placing the order.')
                    );
                }
            }

            $controller = new Mana_Checkout_Model_Processing_Express_Controller(Mage::app()->getRequest(),
                Mage::app()->getResponse());

            return $controller->placeOrder();
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
    }
}