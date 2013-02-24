<?php
/** 
 * @category    Mana
 * @package     ManaPro_Guestbook
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Guestbook_Resource_Post extends Mana_Db_Resource_Object {
    /**
     * Invoked during resource model creation process, this method associates this resource model with model class
     * and with DB table name
     */
	protected function _construct() {
        $this->_init('manapro_guestbook/post', 'id');
        $this->_isPkAutoIncrement = false;
    }
    public function setStatuses($ids, $status) {
        $ids = implode(',', $ids);
        $this->_getWriteAdapter()->query("UPDATE {$this->getMainTable()} SET status = {$status} WHERE id IN ({$ids})");
    }
    protected function _addEditedData($object, $fields, $useDefault) {
        Mage::helper('mana_db')->updateDefaultableField($object, 'created_at', 0, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'store_id', 0, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'status', 0, $fields, $useDefault);
        foreach (Mage::helper('manapro_guestbook')->getVisibleFields() as $field) {
            $method = "_addEditedField_$field";
            $this->$method($object, $fields, $useDefault);
        }
    }
    protected function _addEditedField_email($object, $fields, $useDefault) {
        Mage::helper('mana_db')->updateDefaultableField($object, 'email', 0, $fields, $useDefault);
    }
    protected function _addEditedField_url($object, $fields, $useDefault) {
        Mage::helper('mana_db')->updateDefaultableField($object, 'url', 0, $fields, $useDefault);
    }
    protected function _addEditedField_name($object, $fields, $useDefault) {
        Mage::helper('mana_db')->updateDefaultableField($object, 'name', 0, $fields, $useDefault);
    }
    protected function _addEditedField_text($object, $fields, $useDefault) {
        Mage::helper('mana_db')->updateDefaultableField($object, 'text', 0, $fields, $useDefault);
    }
    protected function _addEditedField_country($object, $fields, $useDefault) {
        Mage::helper('mana_db')->updateDefaultableField($object, 'country_id', 0, $fields, $useDefault);
    }
    protected function _addEditedField_region($object, $fields, $useDefault) {
        if (Mage::getStoreConfigFlag('manapro_guestbook/region/is_freeform')) {
            Mage::helper('mana_db')->updateDefaultableField($object, 'region', 0, $fields, $useDefault);
        }
        else {
            Mage::helper('mana_db')->updateDefaultableField($object, 'region_id', 0, $fields, $useDefault);
        }
    }
}