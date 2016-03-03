<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: Resources/Single model DB operations */
/**
 * This resource model handles DB operations with a single model of type Local_Manadev_Model_Download_Failure. All 
 * database specific code for Local_Manadev_Model_Download_Failure should go here.
 * @author Mana Team
 */
class Local_Manadev_Resource_Download_Status extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Invoked during resource model creation process, this method associates this resource model with model class
     * and with DB table name
     */
	protected function _construct() {
        $this->_setMainTable('downloadable/link_purchased_item');
        $this->_isPkAutoIncrement = false;
    }

    public function changeStatus($id, $status) {
        $db = $this->_getWriteAdapter();
        $db->update($this->getMainTable(), array('status' => $status), $db->quoteInto('item_id = ?', $id));
    }
}