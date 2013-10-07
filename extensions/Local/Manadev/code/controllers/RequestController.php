<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * This controller's actions are available using relative url actions/mana/... 
 * @author Mana Team
 *
 */
class Local_Manadev_RequestController extends Mage_Core_Controller_Front_Action {
	/**
	 * Returns object containing current user's customer data
	 * @return Mage_Customer_Model_Session
	 */
	protected function _getCustomerSession() {
		return Mage::getSingleton('customer/session');
	}
	/**
	 * Returns object containing current cart data
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getCheckoutSession() {
		return Mage::getSingleton('checkout/session');
	}
	public function sendAction() {
		global $_FILES;
		
		if (!$this->_getCustomerSession()->isLoggedIn()) throw new Mage_Core_Exception($this->__('Customer must be logged in.'));

		// save a record in database
		/* @var $request Local_Manadev_Model_Request */ $request = Mage::getModel(strtolower('Local_Manadev/Request'));
		$request
			->setDescription($this->getRequest()->getPost('description'))
			->setCustomerId($this->_getCustomerSession()->getCustomerId())
			->save();
			
		// handle file uploads
		$filesXml = '<data>';
		if (isset($_FILES['file'])) {
			for ($i = 0; ; $i++) {
				if (!isset($_FILES['file']['size'][$i])) break;
				if ($_FILES['file']['size'][$i]) {
					$sourceFile = $_FILES['file']['tmp_name'][$i];
					$targetFile = Mage::getBaseDir('media').DS.'requests'.DS.'files'.DS.$request->getId().DS.$_FILES['file']['name'][$i];
					if (!is_dir(dirname($targetFile)) && !mkdir(dirname($targetFile), 0777, true)) throw new Mage_Core_Exception ('Access denied.');
					move_uploaded_file($sourceFile, $targetFile);
					$filesXml .= "<file>{$_FILES['file']['name'][$i]}</file>";
				}
			}
		}
		$filesXml .= '</data>';
		$request->setFiles($filesXml)->save();
		
		// send emails
		$this->_sendEmail('local_manadev_emails/new_request_to_customer', 
			array('name' => $request->getCustomer()->getName(), 'email' => $request->getCustomer()->getEmail()),
			array(
				'request' => $request,
				'customer' => $request->getCustomer(),
			));
		$this->_sendEmail('local_manadev_emails/new_request_to_owner', null,
			array(
				'request' => $request,
				'customer' => $request->getCustomer(),
			));
			
		// set a notice and redirect
		$this->_getCheckoutSession()->addSuccess($this->__('Your request has been recorded. We will keep you informed as you request is reviewed. Thank you!'));
		$this->_redirect('');
	}
	
    protected function _getEmails($configPath)
    {
        $data = Mage::getStoreConfig($configPath);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }
	
    /**
     * Sends email to customer that request is received
     * @param Local_Manadev_Model_Request $request
     * @return Local_Manadev_RequestController
     */
    protected function _sendEmail($type, $recipients, $vars = array()) {
        if (!Mage::getStoreConfigFlag("$type/enabled")) return $this;

        // turn off inline translations
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $copyTo = $this->_getEmails("$type/copy_to");
        $copyMethod = Mage::getStoreConfig("$type/copy_method");
        if ($copyTo && $copyMethod == 'bcc') {
            foreach ($copyTo as $email) {
                $mailTemplate->addBcc($email);
            }
        }

		$template = Mage::getStoreConfig("$type/template");
		if ($recipients) {
        	$sendTo = isset($recipients['email']) ? array($recipients) : $recipients;
		}
		else {
			$sendTo = array();
        	$to = $this->_getEmails("$type/to");
			foreach ($to as $email) {
                $sendTo[] = array('email' => $email, 'name'  => null);
            }
            if (count($sendTo) == 0) {
                $sendTo[] = array('email' => Mage::getStoreConfig("contacts/email/recipient_email"), 'name'  => null);
            }
		}
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = array('email' => $email, 'name'  => null);
            }
        }

        foreach ($sendTo as $recipient) {
            $mailTemplate
            	->setDesignConfig(array('area'=>'frontend'))
                ->sendTransactional($template, Mage::getStoreConfig("$type/identity"), 
                	$recipient['email'], $recipient['name'], $vars);
        }

        $translate->setTranslateInline(true);
        return $this;
	}
}