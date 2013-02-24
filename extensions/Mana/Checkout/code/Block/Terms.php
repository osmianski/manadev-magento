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
class Mana_Checkout_Block_Terms extends Mage_Checkout_Block_Agreements {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mana/checkout/terms.phtml');
    }
}