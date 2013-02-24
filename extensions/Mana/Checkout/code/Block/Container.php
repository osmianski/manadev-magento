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
class Mana_Checkout_Block_Container extends Mage_Core_Block_Template {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mana/checkout/container.phtml');
    }
    protected function _beforeToHtml() {
        /* @var $js Mana_Core_Helper_Js */ $js = Mage::helper('mana_core/js');
        $js->options('.m-checkout', array(
            'placeOrderUrl' => Mage::getUrl('*/smart/placeOrder'),
            'debug' => 1,
        ));
        return parent::_beforeToHtml();
    }
    public function isCustomerLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
}