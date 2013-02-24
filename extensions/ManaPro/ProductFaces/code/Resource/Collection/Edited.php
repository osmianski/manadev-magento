<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * This collection is used when rendering representing products grid during AJAX refresh and when we have grid
 * data on client passed as a parameter 
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Resource_Collection_Edited extends Mana_Core_Resource_Virtual_Collection {
	protected $_product;
	public function setProduct($value) {
		$this->_product = $value;
		return $this;
	}
	protected $_attributesCodes = array('sku', 'name');
	public function addAttributeToSelect($attribute) {
		$this->_attributesCodes[] = $attribute;
		return $this;
	}
	protected $_clientData;
	public function setClientData($value) {
		$this->_clientData = $value;
		return $this;
	}
    protected function _addMissingOriginalItems() {
    	foreach ($this->_clientData as $key => $data) {
    		$data['entity_id'] = $key;
    		$this->addItem(new Varien_Object($data));
    	}
    	return $this;
    }
}