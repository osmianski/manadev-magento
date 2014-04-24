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

    public function getPostActionUrl() {
        $referer = Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true));
        $referer = Mage::helper('core')->urlEncode($referer);

        return Mage::getUrl('actions/account/loginPost', array(
            Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME => $referer
        ));
    }
}