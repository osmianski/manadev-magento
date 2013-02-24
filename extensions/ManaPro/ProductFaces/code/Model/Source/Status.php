<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enumerates possible roduct statuses
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Model_Source_Status extends ManaPro_ProductFaces_Model_Source_Abstract {
    /**
     * Retrieve all options array
     *
     * @return array
     */
    protected function _getAllOptions()
    {
        return array(
            array(
            	'label' => Mage::helper('catalog')->__('Disabled'),
                'value' =>  Mage_Catalog_Model_Product_Status::STATUS_DISABLED
            ),
            array(
            	'label' => Mage::helper('catalog')->__('Enabled'),
                'value' =>  Mage_Catalog_Model_Product_Status::STATUS_ENABLED
            ),
        );
    }
}