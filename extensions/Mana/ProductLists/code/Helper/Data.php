<?php
/**
 * @category    Mana
 * @package     Mana_ProductLists
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for Mana_ProductLists module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_ProductLists_Helper_Data extends Mage_Core_Helper_Abstract {
    public function decodeGridSerializedInput($encoded)
    {
        $result = array();
        parse_str($encoded, $decoded);
        foreach($decoded as $key => $value) {
			$result[$key] = null;
            parse_str(base64_decode($value), $result[$key]);
        }
        return $result;
    }
    public function createCollection($collectionType, $product, $purpose) {
    	$collection = Mage::getResourceModel($collectionType);
    	
        $collection
        	->setLinkModel($product->getLinkInstance()->setLinkTypeId($collection->getLinkTypeId()))
        	->setProduct($product)
        	->addAttributeToSelect('*')
        	->getSelect()->distinct(true);
        if ($purpose == 'editable_data') {
        	$collection->setIsStrongMode();
        }
        elseif ($purpose == 'frontend_data') {
        	$collection
        		->setIsStrongMode()
        		->addAttributeToFilter('required_options', array('notnull' => true))
            	->addAttributeToSort('position', 'asc');
        	
        }
        elseif ($purpose == 'all_data') {
        }
        else {
        	throw new Exception('Not implemented');
        }
        return $collection;
    }
}