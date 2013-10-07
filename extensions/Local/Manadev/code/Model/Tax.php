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
class Local_Manadev_Model_Tax extends Mage_Tax_Model_Sales_Total_Quote_Tax {
    protected function _totalBaseCalculation(Mage_Sales_Model_Quote_Address $address, $taxRateRequest) {
        if (Mage::getSingleton('checkout/session')->hasMCountryId()) {
            $taxRateRequest->setCountryId(Mage::getSingleton('checkout/session')->getMCountryId());
        }
        if (Mage::getSingleton('checkout/session')->hasMIsVatValid()) {
            $taxRateRequest->setCustomerClassId(Mage::helper('mana_vat')->getCustomerClassId(
                Mage::getSingleton('checkout/session')->getMIsVatValid()
            ));
        }
        parent::_totalBaseCalculation($address, $taxRateRequest);
    }

}