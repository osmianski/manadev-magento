<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Resource_License_Request_Log extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct() {
        $this->_init('local_manadev/license_request_log', 'id');
    }

    public function logRequest() {
        $ip = $_SERVER['REMOTE_ADDR'];
        Mage::getModel('local_manadev/license_request_log')
            ->setData('ip_address', $ip)
            ->save();
    }

    public function hasExceededRequestLimit() {
        $ip_address = $_SERVER['REMOTE_ADDR'];

        $select = $this->_getWriteAdapter()->select();
        $select
            ->from($this->getMainTable(), array())
            ->where("ip_address = ?", $ip_address)
            ->columns(array(
                'COUNT(*)'
            ));
        $count = $this->_getReadAdapter()->fetchOne($select);
        return $count > 15;
    }

    public function deleteOldRequestLogs() {
        $table = $this->getMainTable();
        $this->_getWriteAdapter()->query("
            DELETE FROM {$table} WHERE created_at < (NOW() - INTERVAL 1 HOUR)
        ");
    }

}