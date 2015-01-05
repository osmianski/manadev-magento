<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Rewrite_Catalog_Config extends Mage_Catalog_Model_Config {
    public function getAttributeUsedForSortByArray()
    {
        $options = array(
            'position' => Mage::helper('catalog')->__('Position')
        );
        foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
            $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        }
        //
        return $options;
    }
}