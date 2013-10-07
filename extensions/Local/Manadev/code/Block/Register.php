<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Contains initial data for register dialog 
 * @author Mana Team
 *
 */
class Local_Manadev_Block_Register extends Mage_Core_Block_Template {
	/**
	 * Returns object containing current user's customer data
	 * @return Mage_Customer_Model_Session
	 */
	public function getCustomerSession() {
		return Mage::getSingleton('customer/session');
	}
	public function getRegisterUrl() {
		return $this->getUrl('actions/customer/registerAndLogin');
	}
	public function getLoginUrl() {
		return $this->getUrl('actions/customer/login');
	}
	/**
	 * Add/remove blocks here and invoke methods of existing blocks. This one is called when this block is being 
	 * added to block tree, so it is crucial that this module defines all its dependencies in 
	 * etc/modules/<module name>.xml  
	 */
	protected function _prepareLayout() {
		// pass string translations and options (server side data which is constant to client-side script) 
		/* @var $js Mana_Core_Helper_Js */ $js = Mage::helper('mana_core/js');
		$js->translations(array(
			'Register Now!', 'Proceed',
		));
		$js->options("#register-dialog", array(
			'customerIsLoggedIn' => $this->getCustomerSession()->isLoggedIn(),
			'registerUrl' => $this->getRegisterUrl(),
			'loginUrl' => $this->getLoginUrl(),
		));
	} 
}