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

    public function getIdsByAttributeId($attributeId) {
        $db = $this->getReadConnection();
        $select = $db->select()
            ->from(array('ap_s' => $this->getTable('mana_attributepage/attributePage_store')), 'id')
            ->joinInner(array('ap_g' => $this->getTable('mana_attributepage/attributePage_global')),
                "`ap_g`.`id` = `ap_s`.`attribute_page_global_id`", null)
            ->joinInner(array('ap_gcs' => $this->getTable('mana_attributepage/attributePage_globalCustomSettings')),
                "`ap_gcs`.`id` = `ap_g`.`attribute_page_global_custom_settings_id`", null)
            ->where("`ap_gcs`.`attribute_id_0` = ? OR ".
                "`ap_gcs`.`attribute_id_1` = ? OR " .
                "`ap_gcs`.`attribute_id_2` = ? OR " .
                "`ap_gcs`.`attribute_id_3` = ? OR " .
                "`ap_gcs`.`attribute_id_4` = ?", $attributeId);
        return $db->fetchCol($select);
    }

    public function getIdsByGlobalCustomSettingsId($globalCustomSettingsId) {
        $db = $this->getReadConnection();
        $select = $db->select()
            ->from(array('ap_s' => $this->getTable('mana_attributepage/attributePage_store')), 'id')
            ->joinInner(array('ap_g' => $this->getTable('mana_attributepage/attributePage_global')),
                "`ap_g`.`id` = `ap_s`.`attribute_page_global_id`", null)
            ->where("`ap_g`.`attribute_page_global_custom_settings_id` = ?", $globalCustomSettingsId);
        return $db->fetchCol($select);
    }

    public function getIdsByGlobalId($globalId) {
        $db = $this->getReadConnection();
        $select = $db->select()
            ->from(array('ap_s' => $this->getTable('mana_attributepage/attributePage_store')), 'id')
            ->where("`ap_s`.`attribute_page_global_id` = ?", $globalId);
        return $db->fetchCol($select);
    }

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(Mana_AttributePage_Model_AttributePage_Store::ENTITY, 'id');
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
            throw new Exception($this->attributePageHelper()->__(
                "You must call setData('store_id', ...) before calling load() on %s objects.",
                get_class($object)));
        }
        $db = $this->_getReadAdapter();
        $select = $db->select()
            ->from(array('main_table' => $this->getMainTable()));

        $fields = array();
        $tables = array();

        if (!$object->getData('_skip_non_defaultables')) {
            $tables['ap_g'] = true;
            $tables['ap_gcs'] = true;
            $fields = array_merge($fields, array(
                'attribute_id_0' => "`ap_gcs`.`attribute_id_0`",
                'attribute_id_1' => "`ap_gcs`.`attribute_id_1`",
                'attribute_id_2' => "`ap_gcs`.`attribute_id_2`",
                'attribute_id_3' => "`ap_gcs`.`attribute_id_3`",
                'attribute_id_4' => "`ap_gcs`.`attribute_id_4`",
            ));
        }

        if (isset($tables['ap_g'])) {
            $select->joinInner(array('ap_g' => $this->getTable('mana_attributepage/attributePage_global')),
                "`ap_g`.`id` = `main_table`.`attribute_page_global_id`", null);
        }
        if (isset($tables['ap_gcs'])) {
            $select->joinInner(
                array('ap_gcs' => $this->getTable('mana_attributepage/attributePage_globalCustomSettings')),
                "`ap_gcs`.`id` = `ap_g`.`attribute_page_global_custom_settings_id`", null);
        }
        $select
            ->columns($this->dbHelper()->wrapIntoZendDbExpr($fields))
            ->where("`main_table`.`$field`=?", $value)
            ->where("`main_table`.`store_id`=?", $object->getData('store_id'));

        return $select;
    }
}