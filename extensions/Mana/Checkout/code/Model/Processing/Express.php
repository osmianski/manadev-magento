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

            return Mage::getSingleton('mana_checkout/processing_express_controller')->placeOrder();
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