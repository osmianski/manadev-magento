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
class Mana_Checkout_Block_Payment_Cvv extends Mage_Core_Block_Template {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mana/checkout/payment/cvv.phtml');
    }
}