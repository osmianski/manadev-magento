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
                ->where("parent_id IN (?)", implode(",", $parentIds));
            $data = $db->fetchAll($select);
            $parentIds = array();
            foreach ($data as $record) {
                $parentIds[] = $record['id'];
                $affectedRecords[] = $record['id'];
            }
        }

        return $affectedRecords;
    }
}