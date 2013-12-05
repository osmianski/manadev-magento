<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Model_Source_Attribute extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute');
        $db = $collection->getConnection();

        $select = $collection->getSelect();
        $select
            ->distinct(true)
            ->reset('columns')
            ->columns(array('main_table.attribute_id', 'main_table.frontend_label'))
            ->where("additional_table.is_filterable <> 0")
            ->where(sprintf('(%s) OR (%s) OR (%s)',
                $db->quoteInto('main_table.backend_model = ?', 'eav/entity_attribute_backend_array'),
                $db->quoteInto('main_table.source_model = ?', 'eav/entity_attribute_source_table'),
                $db->quoteInto("main_table.frontend_input = ? AND main_table.source_model IS NOT NULL", 'select')
            ))
            ->order('main_table.frontend_label ASC');

        $result = array(array('value' => '', 'label' => ''));
        foreach ($db->fetchPairs($select) as $value => $label) {
            $result[] = array('value' => $value, 'label' => $label);
        }

        return $result;
    }
}