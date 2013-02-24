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
class Mana_Theme_Block_Crosssell extends Mage_Checkout_Block_Cart_Crosssell
{
    public function setMaxItemCount($value) {
        $this->_maxItemCount = $value;
        return $this;
    }
}