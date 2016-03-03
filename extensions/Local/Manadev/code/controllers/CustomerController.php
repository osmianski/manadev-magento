<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * This controller's actions are available using relative url actions/customer/... 
 * @author Mana Team
 *
 */
class Local_Manadev_CustomerController extends Mage_Core_Controller_Front_Action {
    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
	/**
	 * AJAX remote call. Customer registration from within jQuery form. Copied from 
	 * Mage_Customer_AccountController::createPostAction(). Changed code marked with comments
	 */
	public function registerAndLoginAction() {
		try {
	        $session = $this->_getSession();
	        // MANA BEGIN: throw exception (standard code feels ok with logged in customers)
	        if ($session->isLoggedIn()) throw new Exception($this->__('Customer is not expected to be logged in.'));
	        // MANA END
	        $session->setEscapeMessages(true); // prevent XSS injection in user input
	        if ($this->getRequest()->isPost()) {
	            $errors = array();
	
	            /* @var $customer Mage_Customer_Model_Customer */
	            if (!($customer = Mage::registry('current_customer'))) {
	                $customer = Mage::getModel('customer/customer')->setId(null);
	            }
	
	            /* @var $customerForm Mage_Customer_Model_Form */
	            $customerForm = Mage::getModel('customer/form');
	            $customerForm->setFormCode('customer_account_create')
	                ->setEntity($customer);
	
		        // MANA BEGIN: map field names
	            $data = $this->getRequest()->getPost();
		        $data['firstname'] = $data['customer_name']; 
		        $data['email'] = $data['customer_email']; 
	            //$customerData = $customerForm->extractData($data);
	            $customerData = $data;
		        // MANA END
	            
	            if ($this->getRequest()->getParam('is_subscribed', false)) {
	                $customer->setIsSubscribed(1);
	            }
	
	            /**
	             * Initialize customer group id
	             */
	            $customer->getGroupId();
	
	            if ($this->getRequest()->getPost('create_address')) {
	                /* @var $address Mage_Customer_Model_Address */
	                $address = Mage::getModel('customer/address');
	                /* @var $addressForm Mage_Customer_Model_Form */
	                $addressForm = Mage::getModel('customer/form');
	                $addressForm->setFormCode('customer_register_address')
	                    ->setEntity($address);
	
	                $addressData    = $addressForm->extractData($this->getRequest(), 'address', false);
	                $addressErrors  = $addressForm->validateData($addressData);
	                if ($addressErrors === true) {
	                    $address->setId(null)
	                        ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
	                        ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
	                    $addressForm->compactData($addressData);
	                    $customer->addAddress($address);
	
	                    $addressErrors = $address->validate();
	                    if (is_array($addressErrors)) {
	                        $errors = array_merge($errors, $addressErrors);
	                    }
	                } else {
	                    $errors = array_merge($errors, $addressErrors);
	                }
	            }
	
	            try {
	                $customerErrors = $customerForm->validateData($customerData);
	                if ($customerErrors !== true) {
	                    $errors = array_merge($customerErrors, $errors);
	                } else {
	                    $customerForm->compactData($customerData);
	                    // MANA BEGIN: changed field names
	                    $customer->setPassword($this->getRequest()->getPost('customer_password'));
	                    $customer->setPasswordConfirmation($this->getRequest()->getPost('customer_password_confirmed'));
	                    // MANA END
	                    $customerErrors = $customer->validate();
	                    if (is_array($customerErrors)) {
	                        $errors = array_merge($customerErrors, $errors);
	                    }
	                }
	
	                $validationResult = count($errors) == 0;
	
	                if (true === $validationResult) {
	                    $customer->save();
	
	                    // MANA BEGIN: just log customer in and return AJAX result
	                    $session->setCustomerAsLoggedIn($customer);
	                    return;
	                    // MANA END
	                } else {
	                	// MANA BEGIN: just throw errors as exception
	                    if (is_array($errors)) {
	                    	throw new Exception(implode("\n", $errors));
	                    } else {
	                        throw new Exception($this->__('Invalid customer data'));
	                    }
	                    // MANA END
	                }
	            } catch (Mage_Core_Exception $e) {
	                // MANA BEGIN: just throw errors as exception
	                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
	                    $session->setEscapeMessages(false);
	                	throw new Exception($this->__('There is already an account with this email address.'));
	                } else {
	                	throw new Exception($e->getMessage());
	                }
	                // MANA END
	            }
	            catch (Exception $e) { throw $e; }
	        }
        }
	    catch (Exception $e) {
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('error' => $e->getMessage())));
	    }
	}
	/**
	 * AJAX remote call. Customer log in from within jQuery form. Copied from 
	 * Mage_Customer_AccountController::loginPostAction(). Changed code marked with comments
	 */
	public function loginAction()
    {
		try {
	    	$session = $this->_getSession();
	        // MANA BEGIN: throw exception (standard code feels ok with logged in customers)
	        if ($session->isLoggedIn()) throw new Exception($this->__('Customer is not expected to be logged in.'));
	        // MANA END
	        
	        if ($this->getRequest()->isPost()) {
	            // MANA BEGIN: map field names
	            $data = $this->getRequest()->getPost();
		        $login = array('username' => $data['customer_email'], 'password' => $data['customer_password']); 
		        // MANA END
	            
	            if (!empty($login['username']) && !empty($login['password'])) {
	                try {
			            // MANA BEGIN: just login, no first time welcome
	                	$session->login($login['username'], $login['password']);
				        // MANA END
	                } catch (Mage_Core_Exception $e) {
		                // MANA BEGIN: just throw errors as exception
	                	switch ($e->getCode()) {
	                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
	                            throw new Exception(Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($login['username'])));
	                            break;
	                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
	                            throw new Exception($e->getMessage());
	                            break;
	                        default:
	                            throw new Exception($e->getMessage());
	                    }
				        // MANA END
	                } catch (Exception $e) {
	                	throw new Exception($this->__('Error occured during login.'));
	                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
	                }
	            } else {
	                throw new Exception($this->__('Login and password are required.'));
	            }
	        }
        }
	    catch (Exception $e) {
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('error' => $e->getMessage())));
	    }
	}
}