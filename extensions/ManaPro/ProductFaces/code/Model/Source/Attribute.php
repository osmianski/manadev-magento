<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enumerates all product attributes
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Model_Source_Attribute extends ManaPro_ProductFaces_Model_Source_Abstract {
    /**
     * Retrieve all options array
     *
     * @return array
     */
    protected function _getAllOptions()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');
        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $result = array(array('label' => '', 'value' =>  ''));
        foreach ($db->fetchPairs($collection->getSelect()
        	->distinct(true)
        	->reset(Zend_Db_Select::COLUMNS)
        	->columns(array('attribute_code', 'frontend_label'))
        	->where("frontend_label <> ''")
        	->order('frontend_label')) as $value => $label) 
        {
        	$result[] = array('label' => Mage::helper('manapro_productfaces')->__($label), 'value' =>  $value);
        }
        return $result;
    }
}