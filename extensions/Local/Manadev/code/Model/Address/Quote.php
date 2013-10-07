<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Here are rewrites for memory representation of customer address data
 * @author Mana Team
 *
 */
class Local_Manadev_Model_Address_Quote extends Mage_Sales_Model_Quote_Address {
    /**
     * Validate customer attribute values.
     * For existing customer password + confirmation will be validated only when password is set (i.e. its change is requested)
     *
     * @return bool
     * This method is copied from Mage_Customer_Model_Customer::validate(). Changes marked with comments.
     */
    public function validate()
    {
        $errors = array();
        $helper = Mage::helper('customer');
        $this->implodeStreetAddress();
        if (!Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter the first name.');
        }

       if (empty($errors) || $this->getShouldIgnoreValidation()) {
            return true;
        }
        return $errors;
    }

    protected function hasCountryId() {

        if (Mage::getSingleton('checkout/session')->hasMCountryId()) {
            return true;
        } elseif (($result = parent::getCountryId()) && $result != Mage::getStoreConfig('general/country/default')) {
            return true;
        }
        elseif ($address = Mage::helper('local_manadev')->getCustomerAddress()) {
            return true;
        }
        else {
            return false;
        }
    }

    protected function _cleverGetField($quoteAddressField, $sessionField = null, $customerAddressField = null) {
        if (($pos = strpos($quoteAddressField, '::get')) !== false) {
            $quoteAddressField = substr($quoteAddressField, $pos + strlen('::get'));
        }
        if (!$customerAddressField) {
            $customerAddressField = $quoteAddressField;
        }
        if (!$sessionField) {
            $sessionField = 'M' . $quoteAddressField;
        }

        $hasSessionField = 'has' . $sessionField;
        $getSessionField = 'get' . $sessionField;
        $getQuoteAddressField = 'get' . $quoteAddressField;
        $getCustomerAddressField = 'get' . $customerAddressField;

        if (Mage::getSingleton('checkout/session')->$hasSessionField()) {
            return Mage::getSingleton('checkout/session')->$getSessionField();
        } elseif ($result = parent::$getQuoteAddressField()) {
            return $result;
        }
        elseif ($address = Mage::helper('local_manadev')->getCustomerAddress()) {
            return $address->$getCustomerAddressField();
        }
        else {
            return '';
        }
    }

    public function getCountryId() {
        $result = $this->_cleverGetField(__METHOD__);
        if (!$result) {
            if ($email = $this->getEmail()) {
                $result = Mage::helper('mana_geolocation')->find($email);
                if ($result == 'ZZ' || !$result) {
                    $result = Mage::getStoreConfig('general/country/default');
                }
            }
            else {
                $result = Mage::getStoreConfig('general/country/default');
            }
        }
        return $result;
    }

    public function getCompanyVat() {
        if (Mage::getSingleton('checkout/session')->hasMVat()) {
            return Mage::getSingleton('checkout/session')->getMVat();
        } else {
            return parent::getCompanyVat();
        }
    }

    protected function _customerGetField($field) {
        if (!$this->_getData($field)) {
            /* @var $customerSession Mage_Customer_Model_Session */
            $customerSession = Mage::getSingleton('customer/session');
            if ($customerSession->isLoggedIn()) {
                $this->_data[$field] = $customerSession->getCustomer()->getData($field);
            }
        }
        return $this->_getData($field);
    }
    public function getEmail() {
        return $this->_customerGetField('email');
    }

    public function getFirstname() {
        return $this->_customerGetField('firstname');
    }
}