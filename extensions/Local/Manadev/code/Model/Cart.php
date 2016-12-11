<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Cart extends Mage_Checkout_Model_Cart
{
    public function addProduct($productInfo, $requestInfo = null) {
        return parent::addProduct($productInfo, $requestInfo);
    }

}