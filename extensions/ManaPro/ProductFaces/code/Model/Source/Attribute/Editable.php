<?php

/**
 * Source of product attributes insertable into quick edit columns
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Model_Source_Attribute_Editable extends ManaPro_ProductFaces_Model_Source_Attribute {
	protected function _getAllOptions() {
		$allAttributes = parent::_getAllOptions();
		$result = array();
		foreach (parent::_getAllOptions() as $option) {
			if (!empty($option['value'])) {
	        	$attributes = Mage::getResourceModel('catalog/product_attribute_collection');
	        	$attributes->getSelect()->where('attribute_code = ?', $option['value']);
	        	foreach ($attributes as $attribute) {
				    switch ($attribute->getFrontend()->getInputType()) {
				    	case 'price': 
				        case 'text': 
				        case 'select':
				        	$result[] = $option;
				        	break;
				    }
	        	}
			}
			else {
				$result[] = $option;
			}
		}
		return $result;
	}
}