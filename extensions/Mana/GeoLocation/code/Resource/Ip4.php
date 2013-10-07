<?php
/**
 * @category    Mana
 * @package     Mana_GeoLocation
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_GeoLocation_Resource_Ip4 extends Mage_Core_Model_Mysql4_Abstract {
    protected function _construct() {
        $this->_init('mana_geolocation/ip4', 'id');
    }

    public function truncate() {
        $this->_getWriteAdapter()->truncateTable($this->getMainTable());
    }

    public function run($sql) {
        $this->_getWriteAdapter()->query($sql);
    }

    public function findCountryIdByIp($ip) {
        $ip = explode('.', $ip);
        $value = $ip[3] + $ip[2] * 256 + $ip[1] * 256 * 256 + $ip[0] * 256 * 256 * 256;
        $select = $this->getReadConnection()->select()
            ->from(array('ip' => $this->getMainTable()), 'country_id')
            ->where('ip.ip_from <= ?', $value)
            ->where('ip.ip_to >= ?', $value);
        return $this->getReadConnection()->fetchOne($select);
    }
}