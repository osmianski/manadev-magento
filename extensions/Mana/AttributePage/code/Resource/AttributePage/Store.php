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
class Mana_AttributePage_Resource_AttributePage_Store extends Mana_AttributePage_Resource_AttributePage_Abstract  {
    public function getIdByOptionPageStoreId($optionPageStoreId) {
        $db = $this->getReadConnection();
        return $db->fetchOne($db->select()
            ->from(array('op_s' => $this->getTable('mana_attributepage/optionPage_store')), null)
            ->joinInner(array('op_g' => $this->getTable('mana_attributepage/optionPage_global')),
                "`op_g`.`id` = `op_s`.`option_page_global_id`", null)
            ->joinInner(array('ap_s' => $this->getTable('mana_attributepage/attributePage_store')),
                "`ap_s`.`attribute_page_global_id` = `op_g`.`attribute_page_global_id` AND ".
                "`ap_s`.`store_id` = `op_s`.`store_id`", 'id')
            ->where("`op_s`.`id` = ?", $optionPageStoreId));
    }

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(Mana_AttributePage_Model_AttributePage_Store::ENTITY, 'id');
    }

    protected function _getLoadSelect($field, $value, $object) {
        $db = $this->_getReadAdapter();
        $select = $db->select()
            ->from(array('main_table' => $this->getMainTable()))
            ->joinInner(array('ap_g' => $this->getTable('mana_attributepage/attributePage_global')),
                "`ap_g`.`id` = `main_table`.`attribute_page_global_id`", null)
            ->joinInner(array('ap_gcs' => $this->getTable('mana_attributepage/attributePage_globalCustomSettings')),
                "`ap_gcs`.`id` = `ap_g`.`attribute_page_global_custom_settings_id`",
                array('attribute_id_0', 'attribute_id_1', 'attribute_id_2', 'attribute_id_3', 'attribute_id_4'))
            ->where("`main_table`.`$field`=?", $value);

        return $select;
    }
}