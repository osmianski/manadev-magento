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
class Mana_AttributePage_Resource_AttributePage_Global extends Mana_AttributePage_Resource_AttributePage_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(Mana_AttributePage_Model_AttributePage_Global::ENTITY, 'id');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param   string $field
     * @param   mixed $value
     * @param Varien_Object $object
     * @return  Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        /* @var $select Varien_Db_Select */
        $select = $this->_getReadAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()));

        $fields = array();
        $tables = array();

        if (!$object->getData('_skip_non_defaultables')) {
            $tables['ap_gcs'] = true;
            $fields = array_merge($fields, array(
                'attribute_id_0' => "`ap_gcs`.`attribute_id_0`",
                'attribute_id_1' => "`ap_gcs`.`attribute_id_1`",
                'attribute_id_2' => "`ap_gcs`.`attribute_id_2`",
                'attribute_id_3' => "`ap_gcs`.`attribute_id_3`",
                'attribute_id_4' => "`ap_gcs`.`attribute_id_4`",
                'is_active' => "`ap_gcs`.`is_active`",
                'image' => "`ap_gcs`.`image`",
                'include_in_menu' => "`ap_gcs`.`include_in_menu`",
                'template' => "`ap_gcs`.`template`",
                'show_alphabetic_search' => "`ap_gcs`.`show_alphabetic_search`",
                'page_layout' => "`ap_gcs`.`page_layout`",
                'layout_xml' => "`ap_gcs`.`layout_xml`",
                'custom_design_active_from' => "`ap_gcs`.`custom_design_active_from`",
                'custom_design_active_to' => "`ap_gcs`.`custom_design_active_to`",
                'custom_design' => "`ap_gcs`.`custom_design`",
                'custom_layout_xml' => "`ap_gcs`.`custom_layout_xml`",
            ));
        }
        if ($object->getData('_add_option_page_defaults')) {
            $tables['ap_gcs'] = true;
            $fields = array_merge($fields, array(
                'option_page_include_filter_name' => "`ap_gcs`.`option_page_include_filter_name`",
                'option_page_image' => "`ap_gcs`.`option_page_image`",
                'option_page_include_in_menu' => "`ap_gcs`.`option_page_include_in_menu`",
                'option_page_is_active' => "`ap_gcs`.`option_page_is_active`",
                'option_page_show_products' => "`ap_gcs`.`option_page_show_products`",
                'option_page_available_sort_by' => "`ap_gcs`.`option_page_available_sort_by`",
                'option_page_default_sort_by' => "`ap_gcs`.`option_page_default_sort_by`",
                'option_page_price_step' => "`ap_gcs`.`option_page_price_step`",
                'option_page_page_layout' => "`ap_gcs`.`option_page_page_layout`",
                'option_page_layout_xml' => "`ap_gcs`.`option_page_layout_xml`",
                'option_page_custom_design_active_from' => "`ap_gcs`.`option_page_custom_design_active_from`",
                'option_page_custom_design_active_to' => "`ap_gcs`.`option_page_custom_design_active_to`",
                'option_page_custom_design' => "`ap_gcs`.`option_page_custom_design`",
                'option_page_custom_layout_xml' => "`ap_gcs`.`option_page_custom_layout_xml`",
            ));
        }

        if (isset($tables['ap_gcs'])) {
            $select->joinInner(
                array('ap_gcs' => $this->getTable('mana_attributepage/attributePage_globalCustomSettings')),
                "`ap_gcs`.`id` = `main_table`.`attribute_page_global_custom_settings_id`", null);
        }

        $select
            ->columns($this->dbHelper()->wrapIntoZendDbExpr($fields))
            ->where("`main_table`.`$field`=?", $value);
        return $select;
    }

    /**
     * @param Varien_Object $object
     * @return $this
     */
    public function setDefaults($object) {
        if (!$object->getData('_skip_non_defaultables')) {
            $object
                ->setData('is_active', 1)
                ->setData('include_in_menu', 1)
                ->setData('show_alphabetic_search', 1);
        }
        if ($object->getData('_add_option_page_defaults')) {
            $object
                ->setData('option_page_is_active', 1)
                ->setData('option_page_include_in_menu', 1)
                ->setData('option_page_show_products', 1);
        }
        return $this;
    }
}