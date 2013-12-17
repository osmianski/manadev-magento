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
class Mana_AttributePage_Resource_OptionPage_Store extends Mana_AttributePage_Resource_OptionPage_Abstract  {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(Mana_AttributePage_Model_OptionPage_Store::ENTITY, 'id');
    }

    protected function _getLoadSelect($field, $value, $object) {
        $db = $this->_getReadAdapter();
        $select = $db->select()
            ->from(array('main_table' => $this->getMainTable()))
            ->joinInner(array('op_g' => $this->getTable('mana_attributepage/optionPage_global')),
                "`op_g`.`id` = `main_table`.`option_page_global_id`",
                array('option_id_0', 'option_id_1', 'option_id_2', 'option_id_3', 'option_id_4', 'attribute_page_global_id'))
            ->where("`main_table`.`$field`=?", $value);

        return $select;
    }

    public function getIdsByGlobalCustomSettingsId($globalCustomSettingsId) {
        $db = $this->getReadConnection();
        $select = $db->select()
            ->from(array('op_s' => $this->getTable('mana_attributepage/optionPage_store')), 'id')
            ->joinInner(array('op_g' => $this->getTable('mana_attributepage/optionPage_global')),
                "`op_g`.`id` = `op_s`.`option_page_global_id`", null)
            ->where("`op_g`.`option_page_global_custom_settings_id` = ?", $globalCustomSettingsId);
        return $db->fetchCol($select);
    }

    public function getIdsByGlobalId($globalId) {
        $db = $this->getReadConnection();
        $select = $db->select()
            ->from(array('op_s' => $this->getTable('mana_attributepage/optionPage_store')), 'id')
            ->where("`op_s`.`option_page_global_id` = ?", $globalId);
        return $db->fetchCol($select);
    }
}