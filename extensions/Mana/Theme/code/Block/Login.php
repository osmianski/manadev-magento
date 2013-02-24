<?php
/**
 * @category    Mana
 * @package     Mana_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Theme_Block_Login extends Mage_Customer_Block_Form_Login {
    protected function _prepareLayout() {
        if ($this->getNameInLayout() == 'customer_form_login') {
            $this->getLayout()->getBlock('head')->setTitle(Mage::helper('customer')->__('Customer Login'));
        }
        return $this;
    }
}