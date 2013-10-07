<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Here are rewrites for memory representation of customer data
 * @author Mana Team
 *
 */
class Local_Manadev_Model_Customer extends Mage_Customer_Model_Customer {
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
        $customerHelper = Mage::helper('customer');
        if (!Zend_Validate::is( trim($this->getFirstname()) , 'NotEmpty')) {
            $errors[] = $customerHelper->__('The first name cannot be empty.');
        }

        // MANA BEGIN: not needed
//        if (!Zend_Validate::is( trim($this->getLastname()) , 'NotEmpty')) {
//            $errors[] = $customerHelper->__('The last name cannot be empty.');
//        }
		// MANA END
		
        if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = $customerHelper->__('Invalid email address "%s".', $this->getEmail());
        }

        $password = $this->getPassword();
        if (!$this->getId() && !Zend_Validate::is($password , 'NotEmpty')) {
            $errors[] = $customerHelper->__('The password cannot be empty.');
        }
        if (strlen($password) && !Zend_Validate::is($password, 'StringLength', array(6))) {
            $errors[] = $customerHelper->__('The minimum password length is %s', 6);
        }
        $confirmation = $this->getConfirmation();
        if ($password != $confirmation) {
            $errors[] = $customerHelper->__('Please make sure your passwords match.');
        }

        $entityType = Mage::getSingleton('eav/config')->getEntityType('customer');
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'dob');
        if ($attribute->getIsRequired() && '' == trim($this->getDob())) {
            $errors[] = $customerHelper->__('The Date of Birth is required.');
        }
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'taxvat');
        if ($attribute->getIsRequired() && '' == trim($this->getTaxvat())) {
            $errors[] = $customerHelper->__('The TAX/VAT number is required.');
        }
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'gender');
        if ($attribute->getIsRequired() && '' == trim($this->getGender())) {
            $errors[] = $customerHelper->__('Gender is required.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}