<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enumerates possible rounding methods for inventory sharing
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Model_Source_Yesnoglobal extends ManaPro_ProductFaces_Model_Source_Abstract {
    /**
     * Retrieve all options array
     *
     * @return array
     */
    protected function _getAllOptions()
    {
        return array(
        	array(
            	'label' => Mage::helper('manapro_productfaces')->__('%s (Global Configuration)',
        			Mage::getStoreConfigFlag('manapro_productfaces/cloning/override') 
        				? Mage::helper('manapro_productfaces')->__('Yes')
        				: Mage::helper('manapro_productfaces')->__('No')
        			),
                'value' =>  Mage::getStoreConfigFlag('manapro_productfaces/cloning/override') ? '3' : '2'
            ),
            array(
            	'label' => Mage::helper('manapro_productfaces')->__('Yes'),
                'value' =>  '1'
            ),
            array(
            	'label' => Mage::helper('manapro_productfaces')->__('No'),
                'value' =>  '0'
            ),
        );
    }
}