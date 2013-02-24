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
class Mana_Checkout_Block_Login extends Mage_Customer_Block_Form_Login {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mana/checkout/login.phtml');
    }
}