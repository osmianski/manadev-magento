<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_UrlTemplate extends Mage_Core_Helper_Abstract {
    public function encodeAttribute($data) {
        if (Mage::app()->getStore()->isAdmin() || Mage::getStoreConfigFlag('mana/ajax/debug')) {
            return $data;
        }
        else {
            return $this->urlEncode(Mage::getSingleton('core/url')->sessionUrlVar($data));
        }
    }
}