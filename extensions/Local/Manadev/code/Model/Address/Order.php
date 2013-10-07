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
class Local_Manadev_Model_Address_Order extends Mage_Sales_Model_Order_Address {
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
}