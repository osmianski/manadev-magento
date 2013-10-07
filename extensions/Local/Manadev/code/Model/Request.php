<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: Models/DB-backed model */
/**
 * INSERT HERE: what is this model for 
 * @author Mana Team
 */
class Local_Manadev_Model_Request extends Mage_Core_Model_Abstract {
    /**
     * Invoked during model creation process, this method associates this model with resource and resource
     * collection classes
     */
	protected function _construct() {
		$this->_init(strtolower('Local_Manadev/Request'));
	}
	
	/**
	 * Returns data about customer which sent this request
	 * @return Mage_Customer_Model_Customer
	 */
	public function getCustomer() {
		/* @var $result Mage_Customer_Model_Customer */ $result = Mage::getModel(strtolower('Customer/Customer'));
		$result->load($this->getCustomerId());
		return $result;
	}
	public function getFileNames() {
		$result = array();
		if ($this->hasData('files')) {
			$xml = simplexml_load_string($this->getFiles());
			foreach ($xml->children() as $value) $result[] = (string) $value;
		}
		return implode(', ', $result);
	}
}
