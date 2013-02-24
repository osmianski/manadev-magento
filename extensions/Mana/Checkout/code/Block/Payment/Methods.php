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
class Mana_Checkout_Block_Payment_Methods extends Mage_Checkout_Block_Onepage_Payment_Methods {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mana/checkout/payment/methods.phtml');
    }
}