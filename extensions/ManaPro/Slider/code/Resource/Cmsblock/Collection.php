<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Slider_Resource_Cmsblock_Collection extends Mana_Db_Resource_Object_Collection {
    protected function _construct() {
        $this->_init('manapro_slider/cmsblock');
    }

    public function addBlockColumnsToSelect() {
        foreach (array('name') as $attributeCode) {
            $alias = "t_cmsblock";
            $this->getSelect()->joinLeft(
                array($alias => $this->getTable('cms/block')),
                "$alias.block_id=main_table.block_id",
                array("$alias.title AS cmsblock_name", "$alias.identifier AS cmsblock_identifier"));
        }
        return $this;
    }

    public function getSelectedIds($sessionId) {
        $select = clone $this->getSelect();
        $select
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('main_table.block_id')
            ->where('main_table.edit_session_id = ?', $sessionId);

        return $this->getResource()->getReadConnection()->fetchCol($select);
    }
}