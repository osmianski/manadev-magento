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
class Mana_Content_Resource_Page_Store extends Mana_Content_Resource_Page_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(Mana_Content_Model_Page_Store::ENTITY, 'id');
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param Varien_Object $object
     * @throws Exception
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object) {
        if (!$object->getData('store_id')) {
            throw new Exception($this->coreHelper()->__(
                "You must call setData('store_id', ...) before calling load() on %s objects.",
                get_class($object)));
        }
        $db = $this->_getReadAdapter();
        $select = $db->select()
            ->from(array('main_table' => $this->getMainTable()));

        $select
            ->where("`main_table`.`$field`=?", $value)
            ->where("`main_table`.`store_id`=?", $object->getData('store_id'));

        if($object->getData("_load_global_custom_settings_id")) {
            $select->join(array("mpg" => $this->getTable("mana_content/page_global")), "`mpg`.`id` = `main_table`.`page_global_id`", array("page_global_custom_settings_id"));
//                ->join(array("mpgcs" => $this->getTable("mana_content/page_globalCustomSettings")), "`mpg`.`page_global_custom_settings_id` = `mpgcs`.`id`", array("parent_id"));
        }

        return $select;
    }

    public function getChildPages($parents) {
        $ids = array();
        foreach ($parents as $parent) {
            if(is_null($parent->getData('page_global_custom_settings_id'))) {
                $db = $this->getReadConnection();
                $select = $db->select();
                $select
                    ->from(array('pg' => $this->getTable('mana_content/page_global')), array('page_global_custom_settings_id'))
                    ->where('`pg`.`id` = ?', $parent->getData('page_global_id'));
                $id = $db->fetchOne($select);
                $parent->setData('page_global_custom_settings_id', $id);
            }
            $ids[] = $parent->getData('page_global_custom_settings_id');
        }
        $select = $this->_select();
        $select->where("`ti_gcs`.`parent_id` IN (?)", $ids);

        $store_id = (Mage::app()->getStore()->isAdmin()) ? $this->adminHelper()->getStore()->getId(): Mage::app()->getStore()->getId();

        $select->where("`mps`.`store_id` = ?", $store_id);

        $result = array();
        $db = $this->_getReadAdapter();
        foreach ($db->fetchAll($select) as $data) {
            /* @var $page Mana_Content_Model_Page_Store */
            $page = Mage::getModel('mana_content/page_store');
            $page->setData($data);
            $result[] = $page;
        }

        return $result;
    }

    /**
     * @param $model
     * @return array
     */
    public function getParentPages($model) {
        $result = array();

        $columns = array(
            'id' => '`mps`.`id`',
            'parent_id' => '`ti_gcs`.`parent_id`',
            'title' => '`mps`.`title`',
        );
        $select = $this->_select();
        $select->columns($this->dbHelper()->wrapIntoZendDbExpr($columns));
        $select->where('`mps`.`id` = ?', $model->getId());
        $db = $this->_getReadAdapter();
        $parent = $db->fetchRow($select);

        $result[] = array(
            'id' => $parent['id'],
            'title' => $parent['title'],
        );

        while (true == true) {
            $select = $this->_select();
            $select->columns($this->dbHelper()->wrapIntoZendDbExpr($columns));
            $select->where('`ti_gcs`.`id` = ?', $parent['parent_id']);
            $db = $this->_getReadAdapter();
            $parent = $db->fetchRow($select);

            if(!is_null($parent['id'])) {
                $result[] = array(
                    'id' => $parent['id'],
                    'title' => $parent['title'],
                );
            }

            if (is_null($parent['parent_id'])) {
                break;
            }
        }

        return $result;
    }

    protected function _select($options = array()) {
        $options = array_merge(
            array(
                'skip_non_defaultables' => false,
            ),
            $options
        );

        /* @var $select Zend_Db_Select */
        $db = $this->_getReadAdapter();
        $select = $db->select();
        $select->from(array('mps' => $this->getMainTable()), array());

        $fields = array();
        $tables = array();

        if (!$options['skip_non_defaultables']) {
            $tables['mps'] = true;
            $fields = array_merge(
                $fields,
                array(
                    'id' => "`mps`.`id`",
                    'parent_id' => "`ti_gcs`.`parent_id`",
                    'page_global_custom_settings_id' => "`mpg`.`page_global_custom_settings_id`",
                    'is_active' => "`mps`.`is_active`",
                    'url_key' => "`mps`.`url_key`",
                    'title' => "`mps`.`title`",
                    'content' => "`mps`.`content`",
                    'page_layout' => "`mps`.`page_layout`",
                    'layout_xml' => "`mps`.`layout_xml`",
                    'custom_design_active_from' => "`mps`.`custom_design_active_from`",
                    'custom_design_active_to' => "`mps`.`custom_design_active_to`",
                    'custom_design' => "`mps`.`custom_design`",
                    'custom_layout_xml' => "`mps`.`custom_layout_xml`",
                    'meta_title' => "`mps`.`meta_title`",
                    'meta_keywords' => "`mps`.`meta_keywords`",
                    'meta_description' => "`mps`.`meta_description`",
                    'position' => "`mps`.`position`",
                )
            );
        }
        if (isset($tables['mps'])) {
            $select->joinInner(
                array('mpg' => $this->getTable("mana_content/page_global")),
                "`mpg`.`id` = `mps`.`page_global_id`",
                null
            )
                ->joinInner(
                array('ti_gcs' => $this->getTable('mana_content/page_globalCustomSettings')),
                "`mpg`.`page_global_custom_settings_id` = `ti_gcs`.`id`",
                null
            );
        }
        $select
            ->order("parent_id ASC")
            ->order("position ASC");

        $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));

        return $select;
    }

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }
}