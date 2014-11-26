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
class Mana_Content_Resource_Page_Store_Collection extends Mana_Content_Resource_Page_Abstract_Collection {
    protected function _construct() {
        $this->_init(Mana_Content_Model_Page_Store::ENTITY);
    }

    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        $select = $this->getSelect();

        // join global custom settings if needed
        if ($this->_parentFilterEnabled) {
            $select->joinInner(array('ti_g' => $this->getTable('mana_content/page_global')), '`main_table`.`page_global_id` = `ti_g`.`id`', array("page_global_custom_settings_id"));
            $select->joinInner(array('ti_gcs' => $this->getTable('mana_content/page_globalCustomSettings')),
                "`ti_g`.`page_global_custom_settings_id` = `ti_gcs`.`id`",
                null);

            // add parent condition
            if ($this->_parentId === null) {
                $select->where("`ti_gcs`.`parent_id` IS NULL");
            } else {
                $select->where("`ti_gcs`.`parent_id` = ?", $this->_parentId);
            }
        }

        return $this;
    }

    public function filterTreeByTitle($search) {
        $read = $this->getConnection();
        $select = $this->_prepareSelect();
        if(trim($search) != "") {
            $select->where("`mps`.`title` LIKE ?", '%' . $search . '%');
        }
        $rows = $read->fetchAssoc($select);
        return $this->loadWithParent($rows);
    }

    public function filterTreeByRelatedProducts($related_products = array()) {
        if(!empty($related_products)) {
            $read = $this->getConnection();
            $select = $this->_prepareSelect();
            $select->joinInner(array('mprp' => $this->getTable('mana_content/page_relatedProduct')), "`mpg`.`id` = `mprp`.`page_global_id`", array())
                ->where("`mprp`.`product_id` IN (". implode(",", $related_products) .")");
            $rows = $read->fetchAssoc($select);
            return $this->loadWithParent($rows);
        }
        return array();
    }

    public function filterTreeByTags($tags = array()) {
        if(!empty($tags)) {
            $read = $this->getConnection();
            $select = $this->_prepareSelect();
            $select->joinInner(array('mptr' => $this->getTable('mana_content/page_tagRelation')), "`mptr`.`page_store_id` = `mps`.`id`", array())
                ->where("`mptr`.`page_tag_id` IN (". implode(",", $tags) .")");
            $rows = $read->fetchAssoc($select);
            return $this->loadWithParent($rows);
        }
        return array();
    }

    protected function _prepareSelect() {
        $read = $this->getConnection();
        $select = $read->select();

        $fields = array(
            'id' => new Zend_Db_Expr("`mps`.`id`"),
            'title' => new Zend_Db_Expr("`mps`.`title`"),
            'level' => new Zend_Db_Expr("`mps`.`level`"),
            'parent_id' => new Zend_Db_Expr("`mpgcs`.`parent_id`"),
            'page_global_custom_settings_id' => new Zend_Db_Expr("`mpgcs`.`id`"),
        );

        $select->from(array(
            'mps' => $this->getMainTable()
            ), $fields);
        $select->joinInner(array('mpg' => $this->getTable('mana_content/page_global')), "`mpg`.`id` = `mps`.`page_global_id`", array());
        $select->joinInner(array('mpgcs' => $this->getTable('mana_content/page_globalCustomSettings')), "`mpg`.`page_global_custom_settings_id` = `mpgcs`.`id`", array());
        $select->where("`store_id` = ?", Mage::app()->getStore()->getId());

        // add parent condition
        if ($this->_parentFilterEnabled) {
            if ($this->_parentId === null) {
                $select->where("`mpgcs`.`parent_id` IS NULL");
            } else {
                $select->where("`mpgcs`.`parent_id` = ?", $this->_parentId);
            }
        }
        return $select;
    }

    protected function loadWithParent($rows) {
        $ids = array();
        foreach($rows as $id => $row) {
            array_push($ids, $id);
            $level = $row['level'];
            $parent_ids = array($row['parent_id']);
            while($level > 0) {
                $level --;
                $select = $this->_prepareSelect();
                $select->where("`mps`.`level` = ?", $level);


                $select->where("`mpgcs`.`id` IN (". implode(',', $parent_ids) .")");
                $parent_ids = array();
                $parentRows = $this->getConnection()->fetchAssoc($select);
                foreach($parentRows as $parentId => $parentRow) {
                    if(!in_array($parentId, $ids)) {
                        array_push($ids, $parentId);
                    }
                    array_push($parent_ids, $parentRow['parent_id']);
                }
            }
        }
        return $ids;
    }
}