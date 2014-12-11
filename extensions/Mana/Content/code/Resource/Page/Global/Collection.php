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
class Mana_Content_Resource_Page_Global_Collection extends Mana_Content_Resource_Page_Abstract_Collection {
    protected function _construct() {
        $this->_init(Mana_Content_Model_Page_Global::ENTITY);
    }

    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        $select = $this->getSelect();

        // join global custom settings if needed
        if ($this->_parentFilterEnabled) {
            $select->joinInner(array('ti_gcs' => $this->getTable('mana_content/page_globalCustomSettings')),
                "`main_table`.`page_global_custom_settings_id` = `ti_gcs`.`id`",
                array(
                    'parent_id',
                    'title',
                    'url_key',
                    'content',
                    'position',
                ));
        }

        // add parent condition
        if ($this->_parentFilterEnabled) {
            if ($this->_parentId === null) {
                $select->where("`ti_gcs`.`parent_id` IS NULL");
            }
            else {
                $select->where("`ti_gcs`.`parent_id` = ?", $this->_parentId);
            }
        }

        return $this;
    }
}