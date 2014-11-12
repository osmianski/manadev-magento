<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * This collection is used when rendering representing products grid containing the only "parent" product itself 
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Resource_Collection_Empty extends Mana_Core_Resource_Virtual_Collection {
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
    protected function _addMissingOriginalItems() {
    	$data = array(
    		'entity_id' => 'this',
    		'm_unit' => 'parts',
    		'm_parts' => '1',
    		'position' => '1',
            'm_selling_qty' => '1',
    	);
    	foreach ($this->_attributesCodes as $attribute) {
    		$data[$attribute] = $this->_product->getData($attribute);
    	}
    	$this->addItem(new Varien_Object($data));
    	return $this;
    }
}