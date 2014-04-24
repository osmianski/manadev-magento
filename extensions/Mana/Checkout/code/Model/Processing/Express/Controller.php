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
class Mana_Checkout_Model_Processing_Express_Controller extends Mage_Paypal_Controller_Express_Abstract {
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = 'paypal/config';

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = 'paypal/express_checkout';

    public function placeOrder()
    {
        try {
            $this->_initCheckout();
            $this->_checkout->place($this->_initToken());

            // prepare session to success or cancellation page
            $session = $this->_getCheckoutSession();
            $session->clearHelperData();

            // "last successful quote"
            $quoteId = $this->_getQuote()->getId();
            $session->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            // an order may be created
            $order = $this->_checkout->getOrder();
            if ($order) {
                $session->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());
            }

            // redirect if PayPal specified some URL (for example, to Giropay bank)
            $result = array('success' => true);

            if ($url = $this->_checkout->getRedirectUrl()) {
                $result['redirect'] = $url;
            }
            else {
                $this->_initToken(false); // no need in token anymore
                $result['redirect'] = Mage::getUrl('checkout/onepage/success');
            }
            return $result;
        }
        catch (Mage_Core_Exception $e) {
            $result = array(
                'error' => $e->getMessage(),
            );
        }
        catch (Exception $e) {
            $result = array(
                'error' => $this->__('Unable to place the order.'),
            );
            Mage::logException($e);
        }
        return $result;
    }

    /**
     * Instantiate quote and checkout
     * @throws Mage_Core_Exception
     */
    private function _initCheckout() {
        $quote = $this->_getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Forbidden');
            Mage::throwException(Mage::helper('paypal')->__('Unable to initialize Express Checkout.'));
        }
        $this->_checkout = Mage::getSingleton(
            $this->_checkoutType,
            array(
                'config' => $this->_config,
                'quote' => $quote,
            )
        );
    }

    /**
     * PayPal session instance getter
     *
     * @return Mage_PayPal_Model_Session
     */
    private function _getSession() {
        return Mage::getSingleton('paypal/session');
    }

    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    private function _getCheckoutSession() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sale_Model_Quote
     */
    private function _getQuote() {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }

        return $this->_quote;
    }

    public function getRequest() {
        return Mage::app()->getRequest();
    }
}