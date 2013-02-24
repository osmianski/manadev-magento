<?php
/**
 * @category    Mana
 * @package     ManaPro_DependentDropdowns
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_DependentDropdowns_Model_Source_Attribute extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
                ->setItemObjectClass('catalog/resource_eav_attribute');

        $select = $collection->getSelect();
        $select
            ->distinct(true)
            ->reset('columns')
            ->columns(array('main_table.attribute_id', 'main_table.frontend_label'))
            ->where('main_table.frontend_input = ?', 'select')
            ->order('main_table.frontend_label ASC');

        $result = array(array('value' => '', 'label' => ''));
        foreach($collection->getConnection()->fetchPairs($select) as $value => $label) {
            $result[] = array('value' => $value, 'label' => $label);
        }
        return $result;
    }
}