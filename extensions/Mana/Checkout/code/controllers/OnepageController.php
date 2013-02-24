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
class Mana_Checkout_OnepageController extends Mage_Core_Controller_Front_Action {
    function indexAction() {
        $this->_redirect('checkout', array('_secure' => true));
    }
}
