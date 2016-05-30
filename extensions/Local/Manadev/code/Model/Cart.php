<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Cart extends Mage_Checkout_Model_Cart
{
    public function addProduct($productInfo, $requestInfo = null) {
        if(
            $productInfo->getSku() == Mage::getStoreConfig('local_manadev/support_services_sku') &&
            !isset($requestInfo['m_license'])
        ) {
            Mage::throwException(Mage::helper('local_manadev')->__('Please select one from My Licenses.'));
        }
        return parent::addProduct($productInfo, $requestInfo);
    }

}