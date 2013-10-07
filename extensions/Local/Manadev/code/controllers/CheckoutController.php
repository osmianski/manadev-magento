<?php

include_once 'app/code/core/Mage/Checkout/controllers/OnepageController.php';

class Local_Manadev_CheckoutController extends Mage_Checkout_OnepageController {
	public function saveOrderAction() {
        if ($this->_expireAjax()) {
            return;
        }

        $result = array();
        try {
        	// MANA BEGIN
        	// customer 
        	/* @var $customer Mage_Customer_Model_Customer */ $customer = $this->getOnepage()->getQuote()->getCustomer();
	        $this->getOnepage()->getQuote()->getBillingAddress()->setShouldIgnoreValidation(true);
        	if ($customer->isObjectNew()) {
        		$login = $this->getRequest()->getPost('login');
        		if (!empty($login['username']) && !empty($login['password'])) {
	        		// login
        			$session = Mage::getSingleton('customer/session');
	        		$session->login($login['username'], $login['password']);
	        		$customer = $session->getCustomer();

		        	// billing address
		        	$this->getOnepage()->saveBilling(array(
		        		'firstname' => $customer->getFirstname(),
		        	), false);
        		}
        		else {
        			// register
        			$this->getOnepage()->saveCheckoutMethod('register');

		        	// billing address
		        	$register = $this->getRequest()->getPost('register');
		        	$this->getOnepage()->saveBilling(array(
		        		'firstname' => $register['name'],
		        		'email' => trim($register['username']),
		        		'customer_password' => $register['password'],
		        		'confirm_password' => $register['confirm_password']
		        	), false);
		        	$this->getOnepage()->getQuote()->getBillingAddress()->setShouldIgnoreValidation(true);
        		}
        	}
        	else {
	        	// billing address
	        	$this->getOnepage()->saveBilling(array(
	        		'firstname' => $customer->getFirstname(),
	        	), false);
        	}
        	
        	// payment
        	$this->getOnepage()->savePayment(array(
        		'method' => 'paypal_standard',
        	));
        	
        	// MANA END
        	
            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }
            if ($data = $this->getRequest()->getPost('payment', false)) {
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }
            $this->getOnepage()->saveOrder();
            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            if ($gotoSection = $this->getOnepage()->getCheckout()->getGotoSection()) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }

            if ($updateSection = $this->getOnepage()->getCheckout()->getUpdateSection()) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
        $this->getOnepage()->getQuote()->save();
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
}