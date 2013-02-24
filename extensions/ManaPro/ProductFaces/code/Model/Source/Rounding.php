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
class ManaPro_ProductFaces_Model_Source_Rounding extends ManaPro_ProductFaces_Model_Source_Abstract {
    /**
     * Retrieve all options array
     *
     * @return array
     */
    protected function _getAllOptions()
    {
        return array(
        	array(
            	'label' => Mage::helper('manapro_productfaces')->__('Distribute Rounding Error'),
                'value' =>  'distribute'
            ),
            array(
            	'label' => Mage::helper('manapro_productfaces')->__('Round Down'),
                'value' =>  'down'
            ),
            array(
            	'label' => Mage::helper('manapro_productfaces')->__('Round Up'),
                'value' =>  'up'
            ),
        );
    }
}
