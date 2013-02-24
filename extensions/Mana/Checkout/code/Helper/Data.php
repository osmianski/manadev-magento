<?php
/**
 * @category    Mana
 * @package     Mana_Checkout
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Checkout module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Checkout_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getIsSameShippingAddress() {
        return 1;
    }

    protected $_telephoneSeparator = ' ';
    protected $_telephonePartCount = 3;
    public function implodeTelephone(&$data) {
        if (isset($data['telephone'])) {
            for ($i = 2; $i <= $this->_telephonePartCount; $i++) {
                if (isset($data['telephone' . $i])) {
                    $data['telephone'] .= $this->_telephoneSeparator . $data['telephone' . $i];
                    unset($data['telephone' . $i]);
                }
            }
        }
    }
    public function explodeTelephone($address) {
        if (trim($address->getTelephone())) {
            $parts = explode($this->_telephoneSeparator, trim($address->getTelephone()));
            for ($i = $this->_telephonePartCount; $i >= 2; $i--) {
                if (count($parts) >= $i) {
                    $address->setData('telephone'.$i, array_pop($parts));
                }
            }
            $address->setTelephone(implode($this->_telephoneSeparator, $parts));
        }
    }
}