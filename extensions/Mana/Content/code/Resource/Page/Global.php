<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Resource_Page_Global extends Mana_Content_Resource_Page_Abstract {
    /**
     * @param Mana_Content_Model_Page_Global[] $parents
     * @return Mana_Content_Model_Page_Global[]
     */
    public function getChildPages($parents) {
        $select = $this->_select();

        $ids = array();
        foreach ($parents as $parent) {
            $ids[] = $parent->getData('page_global_custom_settings_id');
        }

        $select->where("`ti_gcs`.`parent_id` IN (?)", $ids);

        $result = array();
        $db = $this->_getReadAdapter();
        foreach ($db->fetchAll($select) as $data) {
            /* @var $page Mana_Content_Model_Page_Global */
            $page = Mage::getModel('mana_content/page_global');
            $page->setData($data);
            $result[] = $page;
        }
        return $result;
    }

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(Mana_Content_Model_Page_Global::ENTITY, 'id');
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
        $select = $this->_select(array(
            'skip_non_defaultables' => $object->getData('_skip_non_defaultables'),
        ));

        $select
            ->where("`main_table`.`$field`=?", $value);

        return $select;
    }

    protected function _select($options = array()) {
        $options = array_merge(array(
            'skip_non_defaultables' => false,
        ), $options);

        /* @var $select Zend_Db_Select */
        $db = $this->_getReadAdapter();
        $select = $db->select();
        $select->from(array('main_table' => $this->getMainTable()));

        $fields = array();
        $tables = array();

        if (!$options['skip_non_defaultables']) {
            $tables['ti_gcs'] = true;
            $fields = array_merge($fields, array(
                'parent_id' => "`ti_gcs`.`parent_id`",
                'is_active' => "`ti_gcs`.`is_active`",
                'url_key' => "`ti_gcs`.`url_key`",
                'title' => "`ti_gcs`.`title`",
                'content' => "`ti_gcs`.`content`",
                'page_layout' => "`ti_gcs`.`page_layout`",
                'layout_xml' => "`ti_gcs`.`layout_xml`",
                'custom_design_active_from' => "`ti_gcs`.`custom_design_active_from`",
                'custom_design_active_to' => "`ti_gcs`.`custom_design_active_to`",
                'custom_design' => "`ti_gcs`.`custom_design`",
                'custom_layout_xml' => "`ti_gcs`.`custom_layout_xml`",
                'meta_title' => "`ti_gcs`.`meta_title`",
                'meta_keywords' => "`ti_gcs`.`meta_keywords`",
                'meta_description' => "`ti_gcs`.`meta_description`",
                'position' => "`ti_gcs`.`position`",
                'reference_id' => '`ti_gcs`.`reference_id`',
                'tags' => '`ti_gcs`.`tags`',
                'default_mask0' => '`ti_gcs`.`default_mask0`',
                'default_mask1' => '`ti_gcs`.`default_mask1`',
                ));
        }
        if (isset($tables['ti_gcs'])) {
            $select->joinInner(
                array('ti_gcs' => $this->getTable('mana_content/page_globalCustomSettings')),
                "`ti_gcs`.`id` = `main_table`.`page_global_custom_settings_id`",
                null
            );
        }
        $select
            ->order("parent_id ASC")
            ->order("position ASC");

        $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));

        return $select;
    }
}