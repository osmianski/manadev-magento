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
class Mana_Checkout_SmartController extends Mage_Core_Controller_Front_Action {
    function placeOrderAction() {
        $result = Mage::getSingleton('mana_checkout/processing')->placeOrder();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
