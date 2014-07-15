<?php
/** 
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Local_Manadev_Block_Express_Review extends Mage_Paypal_Block_Express_Review {
    protected function _beforeToHtml() {
        /* @var $js Mana_Core_Helper_Js */ $js = Mage::helper('mana_core/js');
        $js->options('.m-checkout', array(
            'placeOrderUrl' => Mage::getUrl('checkout/smart/placeExpressOrder'),
            'debug' => 1,
        ));
        return parent::_beforeToHtml();
    }
}