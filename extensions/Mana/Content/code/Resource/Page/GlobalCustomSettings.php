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
class Mana_Content_Resource_Page_GlobalCustomSettings extends Mana_Content_Resource_Page_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(Mana_Content_Model_Page_GlobalCustomSettings::ENTITY, 'id');
    }

    public function getAllChildren($id) {
        $affectedRecords = array($id);
        $db = $this->_getWriteAdapter();

        $select = $db->select()
            ->from($this->getTable('mana_content/page_globalCustomSettings'), null)
            ->columns(
                array(
                    'id' => new Zend_Db_Expr('id'),
                    'level' => new Zend_Db_Expr('level'),
                    'parent_id' => new Zend_Db_Expr('parent_id'),
                )
            )
            ->where("id = ?", $id);
        $editRecord = $db->fetchRow($select);
        $parentIds = array($editRecord['id']);

        while (!empty($parentIds)) {
            $select = $db->select()
                ->from($this->getTable('mana_content/page_globalCustomSettings'), null)
                ->columns(
                    array(
                        'id' => new Zend_Db_Expr('id'),
                    )
                )
                ->where("parent_id IN (". implode(",", $parentIds) .")");
            $data = $db->fetchAll($select);
            $parentIds = array();
            foreach ($data as $record) {
                $parentIds[] = $record['id'];
                $affectedRecords[] = $record['id'];
            }
        }

        return $affectedRecords;
    }

    public function getReferencePages($root_id) {
        $ids = $this->getAllChildren($root_id);
        $read = $this->_getReadAdapter();

        $select = $read->select();
        $select->from(array('pgcs' => $this->getTable('mana_content/page_globalCustomSettings')), array('pgcs.reference_id'))
            ->joinInner(array('pg' => $this->getTable('mana_content/page_global')), 'pg.page_global_custom_settings_id = pgcs.id', array('pg.id'))
            ->where("pgcs.id IN (". implode(',', $ids) .")")
            ->where('pgcs.reference_id IS NOT NULL');

        return $read->fetchAll($select);
    }

    public function getReferencePageUrl($page_store_id) {
        $read = $this->_getReadAdapter();
        $select = $read->select();
        $select->from(array('mps' => $this->getTable('mana_content/page_store')), array())
            ->joinInner(array('mpg' => $this->getTable('mana_content/page_global')), 'mps.page_global_id = mpg.id', array())
            ->joinInner(array('mpgcs' => $this->getTable('mana_content/page_globalCustomSettings')), 'mpg.page_global_custom_settings_id = mpgcs.id', array())
            ->joinInner(array('mpg2' => $this->getTable('mana_content/page_global')), 'mpg2.id = mpgcs.reference_id', array())
            ->joinInner(array('mps2' => $this->getTable('mana_content/page_store')), 'mps2.page_global_id = mpg2.id' ,array('mps2.id'))
            ->where('mps2.store_id = ?', Mage::app()->getStore()->getId())
            ->where('mps.id = ?', $page_store_id);
        if($id = $read->fetchOne($select)) {
            $route = "mana_content/book/view";
            return Mage::getUrl($route, array('_use_rewrite' => true, '_nosid' => true, 'id' => $id));
        }
        return false;
    }
}