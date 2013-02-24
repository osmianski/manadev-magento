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
class Mana_Checkout_Block_Order_Totals extends Mage_Checkout_Block_Cart_Totals {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mana/checkout/order/totals.phtml');
    }
}