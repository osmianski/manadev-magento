<?php
/** 
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

include_once 'app/code/core/Mage/Checkout/controllers/CartController.php';

/**
 * @author Mana Team
 *
 */
class Local_Manadev_Checkout_CartController extends Mage_Checkout_CartController {
    protected function _validateFormKey() {
        return true;
    }
}