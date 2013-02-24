<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enumerates possible units of measures for inventory sharing
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Model_Source_Unit extends ManaPro_ProductFaces_Model_Source_Abstract {
    /**
     * Retrieve all options array
     *
     * @return array
     */
    protected function _getAllOptions()
    {
        return array(
            array(
            	'label' => Mage::helper('manapro_productfaces')->__('Parts of the Whole'),
                'value' =>  'parts'
            ),
            array(
            	'label' => Mage::helper('manapro_productfaces')->__('Percentage of Qty in Stock'),
                'value' =>  'percent'
            ),
            array(
            	'label' => Mage::helper('manapro_productfaces')->__('Exact Qty'),
                'value' =>  'qty'
            ),
            array(
                'label' => Mage::helper('manapro_productfaces')->__('Percentage (Extra Virtual Qty)'),
                'value' => 'virtual_percent'
            ),
        );
    }
}